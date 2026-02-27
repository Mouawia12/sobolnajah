@extends('layoutsadmin.masteradmin')
@section('titlea', trans('accounting.receipt'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ trans('accounting.receipt') }}</h4>
                <button class="btn btn-sm btn-info admin-print-hide" onclick="window.print()">{{ trans('accounting.receipt_page.print') }}</button>
            </div>
            <div class="box-body">
                <div class="admin-form-panel">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>{{ trans('accounting.receipt_page.receipt_code') }}:</strong> {{ $payment->receipt->receipt_code ?? '-' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>{{ trans('accounting.receipt_page.receipt_number') }}:</strong> {{ $payment->receipt_number }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>{{ trans('accounting.receipt_page.date') }}:</strong> {{ optional($payment->paid_on)->format('Y-m-d') }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>{{ trans('accounting.receipt_page.payment_method') }}:</strong> {{ trans('accounting.payment_methods.' . $payment->payment_method) }}
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-15">
                    <table class="table table-bordered">
                        <tbody>
                        <tr>
                            <th style="width: 22%;">{{ trans('accounting.receipt_page.student_name') }}</th>
                            <td>{{ $payment->contract->student->user->name ?? ('Student #' . $payment->contract->student_id) }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('accounting.receipt_page.contract') }}</th>
                            <td>#{{ $payment->contract->id }} - {{ $payment->contract->academic_year }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('accounting.receipt_page.paid_amount') }}</th>
                            <td>{{ number_format((float)$payment->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('accounting.receipt_page.notes') }}</th>
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
