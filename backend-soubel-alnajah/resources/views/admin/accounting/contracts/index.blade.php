@extends('layoutsadmin.masteradmin')
@section('titlea', 'العقود المالية')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">إضافة عقد تلميذ</h4>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('accounting.contracts.store') }}" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label">التلميذ</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">اختر التلميذ</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->user->name ?? ('Student #' . $student->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">السنة الدراسية</label>
                        <input type="text" class="form-control" name="academic_year" value="{{ old('academic_year', date('Y') . '-' . (date('Y') + 1)) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">المبلغ الإجمالي</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="total_amount" value="{{ old('total_amount') }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">خطة الدفع</label>
                        <select name="plan_type" class="form-select" required>
                            <option value="yearly">سنوي</option>
                            <option value="monthly">شهري</option>
                            <option value="installments">دفعات</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">عدد الدفعات</label>
                        <input type="number" min="1" max="24" class="form-control" name="installments_count" value="{{ old('installments_count', 3) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">نموذج الخطة (اختياري)</label>
                        <select name="payment_plan_id" class="form-select">
                            <option value="">بدون</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تاريخ البداية</label>
                        <input type="date" class="form-control" name="starts_on" value="{{ old('starts_on') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">تاريخ النهاية</label>
                        <input type="date" class="form-control" name="ends_on" value="{{ old('ends_on') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">الحالة</label>
                        <select name="status" class="form-select">
                            @foreach(['draft' => 'مسودة', 'active' => 'نشط', 'partial' => 'جزئي', 'paid' => 'مدفوع', 'overdue' => 'متأخر'] as $status => $label)
                                <option value="{{ $status }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ملاحظات</label>
                        <input type="text" class="form-control" name="notes" value="{{ old('notes') }}">
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-primary" type="submit">حفظ العقد</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="box mb-3">
            <div class="box-header with-border">
                <h4 class="box-title">العقود</h4>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="بحث باسم التلميذ/البريد">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach(['draft', 'active', 'partial', 'paid', 'overdue'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary" type="submit">تصفية</button>
                        <a href="{{ route('accounting.contracts.index') }}" class="btn btn-outline-secondary">إعادة ضبط</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>التلميذ</th>
                            <th>السنة</th>
                            <th>الخطة</th>
                            <th>الإجمالي</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                            <th>تعديل</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($contracts as $i => $contract)
                            @php
                                $paidTotal = (float) ($contract->paid_total ?? 0);
                                $remaining = (float) $contract->total_amount - $paidTotal;
                            @endphp
                            <tr>
                                <td>{{ $contracts->firstItem() + $i }}</td>
                                <td>{{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }}</td>
                                <td>{{ $contract->academic_year }}</td>
                                <td>{{ $contract->plan_type }}</td>
                                <td>{{ number_format((float) $contract->total_amount, 2) }}</td>
                                <td>{{ number_format($paidTotal, 2) }}</td>
                                <td>{{ number_format(max($remaining, 0), 2) }}</td>
                                <td><span class="admin-status admin-status-{{ $contract->status }}">{{ $contract->status }}</span></td>
                                <td style="min-width:300px;">
                                    <form method="POST" action="{{ route('accounting.contracts.update', $contract) }}" class="row g-1">
                                        @csrf
                                        @method('PATCH')
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm" name="academic_year" value="{{ $contract->academic_year }}" required>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" class="form-control form-control-sm" min="0" step="0.01" name="total_amount" value="{{ $contract->total_amount }}" required>
                                        </div>
                                        <div class="col-4">
                                            <select name="plan_type" class="form-select form-select-sm" required>
                                                @foreach(['yearly','monthly','installments'] as $planType)
                                                    <option value="{{ $planType }}" @selected($contract->plan_type === $planType)>{{ $planType }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" class="form-control form-control-sm" name="installments_count" value="{{ $contract->installments_count }}">
                                        </div>
                                        <div class="col-4">
                                            <input type="date" class="form-control form-control-sm" name="starts_on" value="{{ optional($contract->starts_on)->format('Y-m-d') }}">
                                        </div>
                                        <div class="col-4">
                                            <input type="date" class="form-control form-control-sm" name="ends_on" value="{{ optional($contract->ends_on)->format('Y-m-d') }}">
                                        </div>
                                        <div class="col-4">
                                            <select name="status" class="form-select form-select-sm" required>
                                                @foreach(['draft','active','partial','paid','overdue'] as $status)
                                                    <option value="{{ $status }}" @selected($contract->status === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" class="form-control form-control-sm" name="notes" value="{{ $contract->notes }}" placeholder="ملاحظات">
                                        </div>
                                        <input type="hidden" name="payment_plan_id" value="{{ $contract->payment_plan_id }}">
                                        <div class="col-12">
                                            <button class="btn btn-sm btn-info" type="submit">تحديث</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="admin-empty-state">لا توجد عقود بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $contracts->links() }}
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">عقود متأخرة</h4>
            </div>
            <div class="box-body">
                <ul>
                    @forelse($overdueContracts as $contract)
                        <li>{{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }} - {{ $contract->academic_year }}</li>
                    @empty
                        <li class="admin-empty-state">لا توجد عقود متأخرة حالياً.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
