@extends('layoutsadmin.masteradmin')
@section('titlea', __('لوحة المحاسب المالي'))

@section('contenta')
<div class="row">
    <div class="col-12 col-md-3">
        <div class="box bg-primary text-white"><div class="box-body"><h6>{{ __('إجمالي العقود') }}</h6><h3>{{ number_format($stats['contracts_count'] ?? 0) }}</h3></div></div>
    </div>
    <div class="col-12 col-md-3">
        <div class="box bg-success text-white"><div class="box-body"><h6>{{ __('العقود النشطة') }}</h6><h3>{{ number_format($stats['active_contracts'] ?? 0) }}</h3></div></div>
    </div>
    <div class="col-12 col-md-3">
        <div class="box bg-danger text-white"><div class="box-body"><h6>{{ __('العقود المتأخرة') }}</h6><h3>{{ number_format($stats['overdue_contracts'] ?? 0) }}</h3></div></div>
    </div>
    <div class="col-12 col-md-3">
        <div class="box bg-info text-white"><div class="box-body"><h6>{{ __('متحصلات اليوم') }}</h6><h3>{{ number_format($stats['payments_today'] ?? 0, 2) }}</h3></div></div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ __('آخر الدفعات') }}</h4>
                <a href="{{ route('accounting.payments.index') }}" class="btn btn-sm btn-primary">{{ __('إدارة الدفعات') }}</a>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('رقم الوصل') }}</th>
                                <th>{{ __('التلميذ') }}</th>
                                <th>{{ __('التاريخ') }}</th>
                                <th>{{ __('المبلغ') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $i => $payment)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $payment->receipt_number }}</td>
                                    <td>{{ optional(optional(optional($payment->contract)->student)->user)->name ?? '—' }}</td>
                                    <td>{{ optional($payment->paid_on)->format('Y-m-d') }}</td>
                                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">{{ __('لا توجد دفعات حديثة') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
