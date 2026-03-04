@extends('layoutsadmin.masteradmin')
@section('titlea', trans('accounting.payments'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.new_payment') }}</h4>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('accounting.payments.store') }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('accounting.payments_page.contract') }}</label>
                        <select name="contract_id" class="form-select" required>
                            <option value="">{{ trans('accounting.payments_page.choose_contract') }}</option>
                            @foreach($contracts as $contract)
                                @php
                                    $studentName = $contract->student->user->name ?? ('Student #' . $contract->student_id);
                                @endphp
                                <option value="{{ $contract->id }}">#{{ $contract->id }} - {{ $studentName }} - {{ $contract->academic_year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.receipt_number') }}</label>
                        <input type="text" class="form-control" name="receipt_number" value="{{ old('receipt_number') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.date') }}</label>
                        <input type="date" class="form-control" name="paid_on" value="{{ old('paid_on', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.amount') }}</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" name="amount" value="{{ old('amount') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">{{ trans('accounting.payments_page.payment_method') }}</label>
                        <select name="payment_method" class="form-select">
                            @foreach(['cash', 'transfer', 'card', 'other'] as $key)
                                <option value="{{ $key }}">{{ trans('accounting.payment_methods.' . $key) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('accounting.payments_page.installment_optional') }}</label>
                        <input type="number" min="1" class="form-control" name="installment_id" value="{{ old('installment_id') }}" placeholder="{{ trans('accounting.payments_page.installment_placeholder') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ trans('accounting.payments_page.notes') }}</label>
                        <input type="text" class="form-control" name="notes" value="{{ old('notes') }}">
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-primary" type="submit">{{ trans('accounting.payments_page.submit_payment') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.filter_title') }}</h4>
            </div>
            <div class="box-body">
                <form method="GET" class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('accounting.payments_page.from_date') }}</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ trans('accounting.payments_page.to_date') }}</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ trans('accounting.payments_page.section') }}</label>
                        <select name="section_id" class="form-select">
                            <option value="">{{ trans('accounting.payments_page.all_sections') }}</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" @selected((string)request('section_id') === (string)$section->id)>
                                    {{ $section->classroom->schoolgrade->name_grade ?? '' }} / {{ $section->classroom->name_class ?? '' }} / {{ $section->name_section ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-info" type="submit">{{ trans('accounting.payments_page.filter') }}</button>
                        <a href="{{ route('accounting.payments.index') }}" class="btn btn-outline-secondary ms-2">{{ trans('accounting.payments_page.reset') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.list_title') }}</h4>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('accounting.payments_page.student') }}</th>
                            <th>{{ trans('accounting.payments_page.receipt_number') }}</th>
                            <th>{{ trans('accounting.payments_page.date') }}</th>
                            <th>{{ trans('accounting.payments_page.amount') }}</th>
                            <th>{{ trans('accounting.payments_page.method') }}</th>
                            <th>{{ trans('accounting.payments_page.action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($payments as $index => $payment)
                            <tr>
                                <td>{{ $payments->firstItem() + $index }}</td>
                                <td>{{ $payment->contract->student->user->name ?? ('Student #' . $payment->contract->student_id) }}</td>
                                <td>{{ $payment->receipt_number }}</td>
                                <td>{{ optional($payment->paid_on)->format('Y-m-d') }}</td>
                                <td>{{ number_format((float)$payment->amount, 2) }}</td>
                                <td>{{ trans('accounting.payment_methods.' . $payment->payment_method) }}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('accounting.payments.receipt', $payment) }}" target="_blank">{{ trans('accounting.payments_page.view_receipt') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="admin-empty-state">{{ trans('accounting.payments_page.empty_payments') }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $payments->links() }}
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('accounting.payments_page.overdue_title') }}</h4>
            </div>
            <div class="box-body">
                <ul>
                    @forelse($overdue as $contract)
                        <li>{{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }} - {{ $contract->academic_year }}</li>
                    @empty
                        <li class="admin-empty-state">{{ trans('accounting.payments_page.empty_overdue') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
