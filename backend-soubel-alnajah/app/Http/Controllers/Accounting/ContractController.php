<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentContractRequest;
use App\Http\Requests\UpdateStudentContractRequest;
use App\Models\Accounting\ContractInstallment;
use App\Models\Accounting\PaymentPlan;
use App\Models\Accounting\StudentContract;
use App\Models\Inscription\StudentInfo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class ContractController extends Controller
{
    public function __construct()
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
            ->with(['student.user', 'student.section.classroom.schoolgrade', 'plan'])
            ->withSum('payments as paid_total', 'amount')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search, function ($query) use ($search) {
                $query->whereHas('student.user', function ($userQuery) use ($search) {
                    $userQuery->where('name->fr', 'like', '%' . $search . '%')
                        ->orWhere('name->ar', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $students = StudentInfo::query()
            ->forSchool($schoolId)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $plans = PaymentPlan::query()->forSchool($schoolId)->where('is_active', true)->orderBy('name')->get();

        $overdueContracts = StudentContract::query()
            ->forSchool($schoolId)
            ->whereHas('installments', function ($query) {
                $query->where('status', 'overdue');
            })
            ->with('student.user')
            ->limit(20)
            ->get();

        return view('admin.accounting.contracts.index', [
            'notify' => $this->notifications(),
            'contracts' => $contracts,
            'students' => $students,
            'plans' => $plans,
            'overdueContracts' => $overdueContracts,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'العقود المالية'],
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

        toastr()->success('تم إنشاء العقد بنجاح');
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

        toastr()->success('تم تحديث العقد');
        return redirect()->route('accounting.contracts.index');
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
}
