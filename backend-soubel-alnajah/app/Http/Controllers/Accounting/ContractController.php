<?php

namespace App\Http\Controllers\Accounting;

use App\Actions\Accounting\ImportAccountingWorkbookAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportAccountingWorkbookRequest;
use App\Http\Requests\StoreStudentContractRequest;
use App\Http\Requests\UpdateStudentContractRequest;
use App\Models\Accounting\ContractInstallment;
use App\Models\Accounting\PaymentPlan;
use App\Models\Accounting\StudentContract;
use App\Models\Inscription\StudentInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Throwable;

class ContractController extends Controller
{
    public function __construct(private ImportAccountingWorkbookAction $importAccountingWorkbookAction)
    {
        $this->middleware(['auth', 'force.password.change']);
    }

    public function index()
    {
        $this->authorize('viewAny', StudentContract::class);
        $this->ensureAccountingRole();

        $schoolId = $this->currentSchoolId();
        $status = request('status');
        $search = request('q');

        $contracts = StudentContract::query()
            ->forSchool($schoolId)
            ->select([
                'id',
                'student_id',
                'payment_plan_id',
                'academic_year',
                'total_amount',
                'external_contract_no',
                'plan_type',
                'installments_count',
                'starts_on',
                'ends_on',
                'status',
                'notes',
                'created_at',
            ])
            ->with([
                'student:id,user_id',
                'student.user:id,name,email',
            ])
            ->withSum('payments as paid_total', 'amount')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($wrappedQuery) use ($search) {
                    $wrappedQuery->where('external_contract_no', 'like', '%' . $search . '%')
                        ->orWhereHas('student.user', function ($userQuery) use ($search) {
                            $userQuery->where('name->fr', 'like', '%' . $search . '%')
                                ->orWhere('name->ar', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $students = StudentInfo::query()
            ->forSchool($schoolId)
            ->select(['id', 'user_id', 'created_at'])
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $plans = PaymentPlan::query()
            ->forSchool($schoolId)
            ->where('is_active', true)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $overdueContracts = StudentContract::query()
            ->forSchool($schoolId)
            ->select(['id', 'student_id', 'academic_year'])
            ->whereHas('installments', function ($query) {
                $query->where('status', 'overdue');
            })
            ->with(['student:id,user_id', 'student.user:id,name'])
            ->limit(20)
            ->get();

        return view('admin.accounting.contracts.index', [
            'notify' => $this->notifications(),
            'contracts' => $contracts,
            'students' => $students,
            'plans' => $plans,
            'overdueContracts' => $overdueContracts,
            'breadcrumbs' => [
                ['label' => trans('accounting.breadcrumbs.dashboard'), 'url' => $this->dashboardUrl()],
                ['label' => trans('accounting.breadcrumbs.contracts')],
            ],
        ]);
    }

    public function store(StoreStudentContractRequest $request)
    {
        $this->authorize('create', StudentContract::class);
        $this->ensureAccountingRole();
        $validated = $request->validated();

        $student = StudentInfo::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($validated['student_id']);

        try {
            DB::transaction(function () use ($validated, $student) {
                $contract = StudentContract::query()->create([
                    'school_id' => $student->section?->school_id,
                    'student_id' => $student->id,
                    'payment_plan_id' => $validated['payment_plan_id'] ?? null,
                    'academic_year' => $validated['academic_year'],
                    'total_amount' => $validated['total_amount'],
                    'plan_type' => $validated['plan_type'],
                    'installments_count' => $validated['installments_count'] ?? null,
                    'starts_on' => $validated['starts_on'] ?? null,
                    'ends_on' => $validated['ends_on'] ?? null,
                    'status' => $validated['status'] ?? 'active',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $this->regenerateInstallments($contract);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('accounting.messages.contract_created'));
        return redirect()->route('accounting.contracts.index');
    }

    public function update(UpdateStudentContractRequest $request, StudentContract $contract)
    {
        $this->authorize('update', $contract);
        $this->ensureAccountingRole();
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $contract) {
                $contract->update([
                    'payment_plan_id' => $validated['payment_plan_id'] ?? null,
                    'academic_year' => $validated['academic_year'],
                    'total_amount' => $validated['total_amount'],
                    'plan_type' => $validated['plan_type'],
                    'installments_count' => $validated['installments_count'] ?? null,
                    'starts_on' => $validated['starts_on'] ?? null,
                    'ends_on' => $validated['ends_on'] ?? null,
                    'status' => $validated['status'],
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => auth()->id(),
                ]);

                if (!$contract->payments()->exists()) {
                    $this->regenerateInstallments($contract);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('accounting.messages.contract_updated'));
        return redirect()->route('accounting.contracts.index');
    }

    public function import(ImportAccountingWorkbookRequest $request)
    {
        $this->authorize('create', StudentContract::class);
        $this->ensureAccountingRole();

        $schoolId = $this->currentSchoolId();
        if (!$schoolId) {
            return back()->withErrors(['file' => trans('accounting.messages.import_school_required')]);
        }

        $isPreview = (bool) $request->boolean('preview');

        try {
            $summary = $this->importAccountingWorkbookAction->execute(
                $request->file('file'),
                (int) $schoolId,
                (int) auth()->id(),
                $isPreview
            );
        } catch (Throwable $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        if (!empty($summary['skipped_rows'])) {
            $filename = $this->writeSkippedRowsReport((array) $summary['skipped_rows']);
            session()->flash('import_report_url', URL::signedRoute('accounting.contracts.import.report', ['filename' => $filename]));
        }

        if ($isPreview) {
            $previewContracts = (array) ($summary['preview_contracts'] ?? []);
            $previewPayments = (array) ($summary['preview_payments'] ?? []);
            $warnings = (array) ($summary['validation_warnings'] ?? []);
            session()->flash('import_preview', [
                'contracts' => $previewContracts,
                'payments' => $previewPayments,
                'validation_warnings' => $warnings,
                'summary' => [
                    'contracts_created' => (int) ($summary['contracts_created'] ?? 0),
                    'contracts_updated' => (int) ($summary['contracts_updated'] ?? 0),
                    'payments_created' => (int) ($summary['payments_created'] ?? 0),
                    'rows_skipped' => (int) ($summary['rows_skipped'] ?? 0),
                    'warnings_count' => count($warnings),
                ],
            ]);

            $previewFilename = $this->writePreviewSnapshotReport($previewContracts, $previewPayments);
            session()->flash('import_preview_csv_url', URL::signedRoute('accounting.contracts.import.report', ['filename' => $previewFilename]));
        }

        $message = trans('accounting.messages.import_summary', [
            'mode' => $isPreview
                ? trans('accounting.messages.import_mode_preview')
                : trans('accounting.messages.import_mode_execute'),
            'created' => (int) ($summary['contracts_created'] ?? 0),
            'updated' => (int) ($summary['contracts_updated'] ?? 0),
            'payments' => (int) ($summary['payments_created'] ?? 0),
            'skipped' => (int) ($summary['rows_skipped'] ?? 0),
            'warnings' => count((array) ($summary['validation_warnings'] ?? [])),
        ]);

        if ($isPreview) {
            toastr()->info($message);
        } else {
            toastr()->success($message);
        }

        return redirect()->route('accounting.contracts.index');
    }

    public function downloadImportReport(Request $request, string $filename)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $this->authorize('viewAny', StudentContract::class);
        $this->ensureAccountingRole();

        if (!preg_match('/^[A-Za-z0-9._-]+\\.csv$/', $filename)) {
            abort(404);
        }

        $path = 'private/accounting-import-reports/' . $filename;
        if (!Storage::exists($path)) {
            abort(404);
        }

        return response()->download(
            storage_path('app/' . $path),
            'accounting-import-skipped-rows.csv',
            ['Content-Type' => 'text/csv']
        );
    }

    private function regenerateInstallments(StudentContract $contract): void
    {
        $contract->installments()->delete();

        if ($contract->plan_type === 'yearly') {
            ContractInstallment::query()->create([
                'contract_id' => $contract->id,
                'installment_no' => 1,
                'due_date' => $contract->starts_on ?? now()->toDateString(),
                'amount' => $contract->total_amount,
                'paid_amount' => 0,
                'status' => 'pending',
                'label' => 'Yearly Payment',
            ]);
            return;
        }

        $count = max(1, (int) ($contract->installments_count ?: 3));
        $baseDate = $contract->starts_on ? Carbon::parse($contract->starts_on) : now();
        $amount = round(((float) $contract->total_amount) / $count, 2);

        for ($i = 1; $i <= $count; $i++) {
            ContractInstallment::query()->create([
                'contract_id' => $contract->id,
                'installment_no' => $i,
                'due_date' => $baseDate->copy()->addMonths($i - 1)->toDateString(),
                'amount' => $i === $count ? ((float) $contract->total_amount - ($amount * ($count - 1))) : $amount,
                'paid_amount' => 0,
                'status' => 'pending',
                'label' => 'Installment ' . $i,
            ]);
        }
    }

    private function ensureAccountingRole(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasRole('accountant'))) {
            abort(403);
        }
    }

    private function dashboardUrl(): string
    {
        $user = auth()->user();
        if ($user && $user->hasRole('accountant') && !$user->hasRole('admin')) {
            return route('accountant.dashboard');
        }

        return url('/admin');
    }

    /**
     * @param array<int,array{sheet:string,row:int,reason:string,contract_no:string,student_name:string}> $rows
     */
    private function writeSkippedRowsReport(array $rows): string
    {
        $filename = 'skipped_rows_' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(6)) . '.csv';
        $path = 'private/accounting-import-reports/' . $filename;

        $lines = ['sheet,row,reason,contract_no,student_name'];
        foreach ($rows as $row) {
            $lines[] = implode(',', [
                $this->csvValue((string) ($row['sheet'] ?? '')),
                $this->csvValue((string) ($row['row'] ?? '')),
                $this->csvValue((string) ($row['reason'] ?? '')),
                $this->csvValue((string) ($row['contract_no'] ?? '')),
                $this->csvValue((string) ($row['student_name'] ?? '')),
            ]);
        }

        Storage::put($path, implode(PHP_EOL, $lines) . PHP_EOL);

        return $filename;
    }

    private function csvValue(string $value): string
    {
        $escaped = str_replace('"', '""', $value);

        return '"' . $escaped . '"';
    }

    /**
     * @param array<int,array{contract_no:string,student_name:string,academic_year:string,total_amount:float,is_new:bool}> $contracts
     * @param array<int,array{receipt_number:string,contract_no:string,type:string,amount:float,paid_on:string}> $payments
     */
    private function writePreviewSnapshotReport(array $contracts, array $payments): string
    {
        $filename = 'preview_snapshot_' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(6)) . '.csv';
        $path = 'private/accounting-import-reports/' . $filename;

        $lines = [];
        $lines[] = '"section","contract_no","student_name","academic_year","total_amount","operation","receipt_number","payment_type","payment_amount","paid_on"';

        foreach ($contracts as $contract) {
            $lines[] = implode(',', [
                $this->csvValue('contracts_preview'),
                $this->csvValue((string) ($contract['contract_no'] ?? '')),
                $this->csvValue((string) ($contract['student_name'] ?? '')),
                $this->csvValue((string) ($contract['academic_year'] ?? '')),
                $this->csvValue((string) ($contract['total_amount'] ?? 0)),
                $this->csvValue(!empty($contract['is_new']) ? 'create' : 'update'),
                $this->csvValue(''),
                $this->csvValue(''),
                $this->csvValue(''),
                $this->csvValue(''),
            ]);
        }

        foreach ($payments as $payment) {
            $lines[] = implode(',', [
                $this->csvValue('payments_preview'),
                $this->csvValue((string) ($payment['contract_no'] ?? '')),
                $this->csvValue(''),
                $this->csvValue(''),
                $this->csvValue(''),
                $this->csvValue(''),
                $this->csvValue((string) ($payment['receipt_number'] ?? '')),
                $this->csvValue((string) ($payment['type'] ?? '')),
                $this->csvValue((string) ($payment['amount'] ?? 0)),
                $this->csvValue((string) ($payment['paid_on'] ?? '')),
            ]);
        }

        Storage::put($path, implode(PHP_EOL, $lines) . PHP_EOL);

        return $filename;
    }
}
