<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFamilyPaymentRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Accounting\ContractInstallment;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentReceipt;
use App\Models\Accounting\StudentContract;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use Illuminate\Http\Request;
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
                ['label' => trans('accounting.breadcrumbs.dashboard'), 'url' => $this->dashboardUrl()],
                ['label' => trans('accounting.breadcrumbs.payments')],
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

        toastr()->success(trans('accounting.messages.payment_created'));
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
            'contract.student.parent.user',
            'contract.student.parent.students.user',
            'contract.student.section.classroom.schoolgrade',
            'contract.student.section.school',
            'contract.payments',
            'receipt',
        ]);

        // دفعات العائلة المرتبطة بنفس الوصل (دفعة شاملة للإخوة)
        $familyPayments = collect([$payment]);
        if ($payment->family_group) {
            $familyPayments = Payment::query()
                ->where('school_id', $payment->school_id)
                ->where('family_group', $payment->family_group)
                ->with([
                    'contract.student.user',
                    'contract.payments',
                ])
                ->orderBy('id')
                ->get();
        }

        return view('admin.accounting.payments.receipt', [
            'notify' => $this->notifications(),
            'payment' => $payment,
            'familyPayments' => $familyPayments,
            'breadcrumbs' => [
                ['label' => trans('accounting.breadcrumbs.dashboard'), 'url' => $this->dashboardUrl()],
                ['label' => trans('accounting.breadcrumbs.payments'), 'url' => route('accounting.payments.index')],
                ['label' => trans('accounting.breadcrumbs.receipt')],
            ],
        ]);
    }

    /**
     * بحث AJAX عن ولي أمر أو تلميذ لتسجيل دفعة عائلية.
     */
    public function familySearch(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        $this->ensureAccountingRole();

        $schoolId = $this->currentSchoolId();
        $type = $request->query('type') === 'parent' ? 'parent' : 'student';
        $search = trim((string) $request->query('q'));

        if (mb_strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        if ($type === 'parent') {
            $parents = MyParent::query()
                ->forSchool($schoolId)
                ->where(function ($query) use ($search) {
                    $query->where('prenomwali->ar', 'like', '%' . $search . '%')
                        ->orWhere('prenomwali->fr', 'like', '%' . $search . '%')
                        ->orWhere('nomwali->ar', 'like', '%' . $search . '%')
                        ->orWhere('nomwali->fr', 'like', '%' . $search . '%')
                        ->orWhere('numtelephonewali', 'like', '%' . $search . '%');
                })
                ->withCount('students')
                ->orderBy('id')
                ->limit(15)
                ->get();

            return response()->json([
                'results' => $parents->map(fn ($parent) => [
                    'id' => $parent->id,
                    'name' => trim($parent->prenomwali . ' ' . $parent->nomwali),
                    'phone' => $parent->numtelephonewali ? '0' . $parent->numtelephonewali : '',
                    'relation' => $parent->relationetudiant,
                    'children_count' => $parent->students_count,
                ])->values(),
            ]);
        }

        $students = StudentInfo::query()
            ->forSchool($schoolId)
            ->where(function ($query) use ($search) {
                $query->where('prenom->ar', 'like', '%' . $search . '%')
                    ->orWhere('nom->ar', 'like', '%' . $search . '%')
                    ->orWhere('prenom->fr', 'like', '%' . $search . '%')
                    ->orWhere('nom->fr', 'like', '%' . $search . '%')
                    ->orWhere('national_id', 'like', '%' . $search . '%')
                    ->orWhere('numtelephone', 'like', '%' . $search . '%');
            })
            ->with([
                'parent:id,prenomwali,nomwali',
                'section:id,classroom_id,name_section',
                'section.classroom:id,grade_id,name_class',
                'section.classroom.schoolgrade:id,name_grade',
            ])
            ->orderBy('nom')
            ->limit(15)
            ->get();

        return response()->json([
            'results' => $students->map(fn ($student) => [
                'id' => $student->id,
                'name' => trim($student->prenom . ' ' . $student->nom),
                'national_id' => $student->national_id,
                'section' => trim(
                    ($student->section->classroom->schoolgrade->name_grade ?? '') . ' / ' .
                    ($student->section->classroom->name_class ?? '') . ' / ' .
                    ($student->section->name_section ?? ''), ' /'
                ),
                'parent_name' => $student->parent ? trim($student->parent->prenomwali . ' ' . $student->parent->nomwali) : '',
            ])->values(),
        ]);
    }

    /**
     * بيانات العائلة: ولي الأمر وكل أبنائه مع عقودهم وأرصدتهم.
     */
    public function familyData(Request $request)
    {
        $this->authorize('viewAny', Payment::class);
        $this->ensureAccountingRole();

        $schoolId = $this->currentSchoolId();
        $type = $request->query('type') === 'parent' ? 'parent' : 'student';
        $id = (int) $request->query('id');

        $targetStudentId = null;
        if ($type === 'parent') {
            $parent = MyParent::query()->forSchool($schoolId)->findOrFail($id);
        } else {
            $student = StudentInfo::query()->forSchool($schoolId)->findOrFail($id);
            $targetStudentId = $student->id;
            $parent = $student->parent_id ? MyParent::query()->find($student->parent_id) : null;
        }

        // كل أبناء ولي الأمر (الإخوة)، أو التلميذ وحده إن لم يكن له ولي مسجّل
        if ($parent) {
            $children = StudentInfo::query()
                ->forSchool($schoolId)
                ->where('parent_id', $parent->id)
                ->with([
                    'section:id,classroom_id,name_section',
                    'section.classroom:id,grade_id,name_class',
                    'section.classroom.schoolgrade:id,name_grade',
                ])
                ->orderBy('datenaissance')
                ->get();
        } else {
            $children = collect([$student->load([
                'section:id,classroom_id,name_section',
                'section.classroom:id,grade_id,name_class',
                'section.classroom.schoolgrade:id,name_grade',
            ])]);
        }

        // آخر عقد لكل ابن مع مجموع المدفوع
        $contracts = StudentContract::query()
            ->forSchool($schoolId)
            ->whereIn('student_id', $children->pluck('id'))
            ->withSum('payments as paid_total', 'amount')
            ->orderBy('academic_year')
            ->orderBy('id')
            ->get()
            ->keyBy('student_id'); // keyBy يحتفظ بالأخير => أحدث عقد لكل تلميذ

        return response()->json([
            'parent' => $parent ? [
                'id' => $parent->id,
                'name' => trim($parent->prenomwali . ' ' . $parent->nomwali),
                'phone' => $parent->numtelephonewali ? '0' . $parent->numtelephonewali : '',
                'relation' => $parent->relationetudiant,
            ] : null,
            'children' => $children->map(function ($child) use ($contracts, $targetStudentId) {
                $contract = $contracts->get($child->id);
                $total = $contract ? (float) $contract->total_amount : 0;
                $paid = $contract ? (float) ($contract->paid_total ?? 0) : 0;

                return [
                    'student_id' => $child->id,
                    'name' => trim($child->prenom . ' ' . $child->nom),
                    'national_id' => $child->national_id,
                    'section' => trim(
                        ($child->section->classroom->schoolgrade->name_grade ?? '') . ' / ' .
                        ($child->section->classroom->name_class ?? '') . ' / ' .
                        ($child->section->name_section ?? ''), ' /'
                    ),
                    'is_target' => $targetStudentId !== null && $child->id === $targetStudentId,
                    'contract' => $contract ? [
                        'id' => $contract->id,
                        'academic_year' => $contract->academic_year,
                        'total' => $total,
                        'paid' => $paid,
                        'remaining' => max($total - $paid, 0),
                        'status' => $contract->status,
                    ] : null,
                ];
            })->values(),
        ]);
    }

    /**
     * تسجيل دفعة عائلية شاملة: دفعة لكل ابن محدد تحت وصل واحد.
     */
    public function storeFamily(StoreFamilyPaymentRequest $request)
    {
        $this->authorize('create', Payment::class);
        $this->ensureAccountingRole();
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId();
        $contractIds = collect($validated['items'])->pluck('contract_id')->map(fn ($v) => (int) $v);

        $contracts = StudentContract::query()
            ->forSchool($schoolId)
            ->whereIn('id', $contractIds)
            ->get()
            ->keyBy('id');

        if ($contracts->count() !== $contractIds->count()) {
            return back()->withErrors(['error' => trans('accounting.family_payment.invalid_contracts')])->withInput();
        }

        // أرقام الوصولات: الأساسي للأول ثم لاحقة /2 /3 ... للإخوة (قيد uniqueness)
        $items = array_values($validated['items']);
        $baseNumber = trim($validated['receipt_number']);
        $receiptNumbers = [];
        foreach ($items as $index => $item) {
            $receiptNumbers[$index] = $index === 0 ? $baseNumber : $baseNumber . '/' . ($index + 1);
        }

        $alreadyUsed = Payment::query()
            ->where('school_id', $contracts->first()->school_id)
            ->whereIn('receipt_number', $receiptNumbers)
            ->exists();
        if ($alreadyUsed) {
            return back()->withErrors(['error' => trans('accounting.family_payment.receipt_used')])->withInput();
        }

        $familyGroup = (string) Str::uuid();
        $firstPayment = null;

        try {
            DB::transaction(function () use ($validated, $items, $contracts, $receiptNumbers, $familyGroup, &$firstPayment) {
                foreach ($items as $index => $item) {
                    $contract = $contracts->get((int) $item['contract_id']);

                    $payment = Payment::query()->create([
                        'school_id' => $contract->school_id,
                        'contract_id' => $contract->id,
                        'installment_id' => null,
                        'receipt_number' => $receiptNumbers[$index],
                        'paid_on' => $validated['paid_on'],
                        'amount' => $item['amount'],
                        'payment_method' => $validated['payment_method'] ?? 'cash',
                        'notes' => $validated['notes'] ?? null,
                        'family_group' => $familyGroup,
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
                            'amount' => $item['amount'],
                            'paid_on' => $validated['paid_on'],
                            'family_group' => $familyGroup,
                            'family_receipt_base' => $validated['receipt_number'],
                        ],
                    ]);

                    $this->refreshContractStatus($contract->fresh(['payments', 'installments']));

                    $firstPayment ??= $payment;
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('accounting.family_payment.created'));

        // فتح الوصل العائلي مباشرة للطباعة
        return redirect()->route('accounting.payments.receipt', $firstPayment);
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

    private function dashboardUrl(): string
    {
        $user = auth()->user();
        if ($user && $user->hasRole('accountant') && !$user->hasRole('admin')) {
            return route('accountant.dashboard');
        }

        return url('/admin');
    }
}
