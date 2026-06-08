<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Payment;
use App\Models\Accounting\StudentContract;

class AccountantDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:accountant', 'force.password.change']);
    }

    public function index()
    {
        $schoolId = $this->currentSchoolId();

        // عدّادات العقود في استعلام واحد بدل ثلاثة
        $contractStats = StudentContract::query()
            ->forSchool($schoolId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(status = 'active') as active")
            ->selectRaw("SUM(status = 'overdue') as overdue")
            ->first();

        $contractsCount = (int) ($contractStats->total ?? 0);
        $activeContracts = (int) ($contractStats->active ?? 0);
        $overdueContracts = (int) ($contractStats->overdue ?? 0);
        $paymentsToday = (float) Payment::query()
            ->forSchool($schoolId)
            ->whereDate('paid_on', now()->toDateString())
            ->sum('amount');

        $recentPayments = Payment::query()
            ->forSchool($schoolId)
            ->select(['id', 'contract_id', 'receipt_number', 'paid_on', 'amount'])
            ->with(['contract:id,student_id', 'contract.student:id,user_id', 'contract.student.user:id,name'])
            ->orderByDesc('paid_on')
            ->limit(8)
            ->get();

        return view('admin.accounting.dashboard', [
            'notify' => $this->notifications(),
            'stats' => [
                'contracts_count' => $contractsCount,
                'active_contracts' => $activeContracts,
                'overdue_contracts' => $overdueContracts,
                'payments_today' => $paymentsToday,
            ],
            'recentPayments' => $recentPayments,
            'breadcrumbs' => [
                ['label' => __('لوحة المحاسب')],
            ],
        ]);
    }
}
