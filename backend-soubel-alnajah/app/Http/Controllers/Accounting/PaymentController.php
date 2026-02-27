<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Accounting\ContractInstallment;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentReceipt;
use App\Models\Accounting\StudentContract;
use App\Models\School\Section;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'force.password.change']);
    }

    public function index()
    {
        $this->authorize('viewAny', Payment::class);
        $this->ensureAccountingRole();

        $schoolId = $this->currentSchoolId();
        $from = request('date_from');
        $to = request('date_to');
        $sectionId = request('section_id');

        $payments = Payment::query()
            ->forSchool($schoolId)
            ->select([
                'id',
                'contract_id',
                'receipt_number',
                'paid_on',
                'amount',
                'payment_method',
            ])
            ->with([
                'contract:id,student_id,academic_year',
                'contract.student:id,user_id,section_id',
                'contract.student.user:id,name',
            ])
            ->when($from, fn ($q) => $q->whereDate('paid_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('paid_on', '<=', $to))
            ->when($sectionId, function ($q) use ($sectionId) {
                $q->whereHas('contract.student', function ($studentQuery) use ($sectionId) {
                    $studentQuery->where('section_id', $sectionId);
                });
            })
            ->orderByDesc('paid_on')
            ->paginate(20)
            ->withQueryString();

        $contracts = StudentContract::query()
            ->forSchool($schoolId)
            ->select(['id', 'student_id', 'academic_year', 'created_at'])
            ->with([
                'student:id,user_id',
                'student.user:id,name',
            ])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $overdue = StudentContract::query()
            ->forSchool($schoolId)
            ->select(['id', 'student_id', 'academic_year', 'updated_at'])
            ->with(['student:id,user_id', 'student.user:id,name'])
            ->whereHas('installments', fn ($q) => $q->where('status', 'overdue'))
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get();

        $sections = Section::query()
            ->forSchool($schoolId)
            ->select(['id', 'classroom_id', 'name_section'])
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->orderBy('id')
            ->get();

        return view('admin.accounting.payments.index', [
            'notify' => $this->notifications(),
            'payments' => $payments,
            'contracts' => $contracts,
            'overdue' => $overdue,
            'sections' => $sections,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'الدفعات والوصولات'],
            ],
        ]);
    }

    public function store(StorePaymentRequest $request)
    {
        $this->authorize('create', Payment::class);
        $this->ensureAccountingRole();
        $validated = $request->validated();

        $contract = StudentContract::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($validated['contract_id']);

        try {
            DB::transaction(function () use ($validated, $contract) {
                $payment = Payment::query()->create([
                    'school_id' => $contract->school_id,
                    'contract_id' => $contract->id,
                    'installment_id' => $validated['installment_id'] ?? null,
                    'receipt_number' => $validated['receipt_number'],
                    'paid_on' => $validated['paid_on'],
                    'amount' => $validated['amount'],
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'notes' => $validated['notes'] ?? null,
                    'received_by' => auth()->id(),
                    'created_by' => auth()->id(),
                ]);

                PaymentReceipt::query()->create([
                    'school_id' => $contract->school_id,
                    'payment_id' => $payment->id,
                    'receipt_code' => 'RCPT-' . strtoupper(Str::random(8)),
                    'issued_at' => now(),
                    'payload' => [
                        'contract_id' => $contract->id,
                        'student_id' => $contract->student_id,
                        'amount' => $validated['amount'],
                        'paid_on' => $validated['paid_on'],
                    ],
                ]);

                if (!empty($validated['installment_id'])) {
                    $installment = ContractInstallment::query()->where('contract_id', $contract->id)->findOrFail($validated['installment_id']);
                    $newPaid = (float) $installment->paid_amount + (float) $validated['amount'];
                    $installment->update([
                        'paid_amount' => $newPaid,
                        'status' => $newPaid >= (float) $installment->amount ? 'paid' : 'partial',
                    ]);
                }

                $this->refreshContractStatus($contract->fresh(['payments', 'installments']));
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success('تم تسجيل الدفعة');
        return redirect()->route('accounting.payments.index');
    }

    public function receipt(Payment $payment)
    {
        $this->authorize('view', $payment);
        $this->ensureAccountingRole();

        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $payment->school_id !== (int) $schoolId) {
            abort(404);
        }

        $payment->load([
            'contract.student.user',
            'contract.student.section.classroom.schoolgrade',
            'receipt',
        ]);

        return view('admin.accounting.payments.receipt', [
            'notify' => $this->notifications(),
            'payment' => $payment,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'الدفعات والوصولات', 'url' => route('accounting.payments.index')],
                ['label' => 'وصل الدفع'],
            ],
        ]);
    }

    private function refreshContractStatus(StudentContract $contract): void
    {
        $paidTotal = (float) $contract->payments()->sum('amount');
        $remaining = (float) $contract->total_amount - $paidTotal;
        $hasOverdueInstallment = $contract->installments()
            ->whereIn('status', ['pending', 'partial'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->exists();

        $status = 'active';
        if ($remaining <= 0) {
            $status = 'paid';
        } elseif ($paidTotal > 0) {
            $status = 'partial';
        }
        if ($remaining > 0 && $hasOverdueInstallment) {
            $status = 'overdue';
        }

        $contract->update(['status' => $status, 'updated_by' => auth()->id()]);
    }

    private function ensureAccountingRole(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasRole('accountant'))) {
            abort(403);
        }
    }
}
