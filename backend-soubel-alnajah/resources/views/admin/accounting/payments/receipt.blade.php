@extends('layoutsadmin.masteradmin')
@section('titlea', 'وصل دفع')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">وصل دفع</h4>
                <button class="btn btn-sm btn-info admin-print-hide" onclick="window.print()">طباعة</button>
            </div>
            <div class="box-body">
                <div class="admin-form-panel">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>كود الوصل:</strong> {{ $payment->receipt->receipt_code ?? '-' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>رقم الوصل:</strong> {{ $payment->receipt_number }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>التاريخ:</strong> {{ optional($payment->paid_on)->format('Y-m-d') }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>طريقة الدفع:</strong> {{ $payment->payment_method }}
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-15">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th style="width: 22%;">اسم التلميذ</th>
                            <td>{{ $payment->contract->student->user->name ?? ('Student #' . $payment->contract->student_id) }}</td>
                        </tr>
                        <tr>
                            <th>العقد</th>
                            <td>#{{ $payment->contract->id }} - {{ $payment->contract->academic_year }}</td>
                        </tr>
                        <tr>
                            <th>المبلغ المدفوع</th>
                            <td>{{ number_format((float)$payment->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>ملاحظات</th>
                            <td>{{ $payment->notes ?: '-' }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .admin-print-hide,
    .main-sidebar,
    .main-header,
    .content-header,
    .main-footer {
        display: none !important;
    }

    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }

    .box {
        box-shadow: none !important;
        border-color: #d5dbe5 !important;
    }
}
</style>
@endsection
