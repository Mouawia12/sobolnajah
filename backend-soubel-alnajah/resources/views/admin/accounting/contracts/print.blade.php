<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>عقد رقم {{ $contract->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; margin: 24px; }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .meta { margin-bottom: 12px; }
        .meta div { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; font-size: 13px; }
        th { background: #f3f3f3; }
        .summary { margin-top: 14px; }
        .actions { margin-top: 16px; }
        .btn { display: inline-block; padding: 8px 12px; border: 1px solid #888; text-decoration: none; color: #111; border-radius: 4px; margin-left: 8px; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <h2>عقد طالب</h2>
    <div class="meta">
        <div><strong>رقم العقد:</strong> {{ $contract->id }}</div>
        <div><strong>الطالب:</strong> {{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }}</div>
        <div><strong>البريد:</strong> {{ $contract->student->user->email ?? '-' }}</div>
        <div><strong>السنة الدراسية:</strong> {{ $contract->academic_year }}</div>
        <div><strong>نوع الخطة:</strong> {{ $contract->plan_type }}</div>
        <div><strong>عدد الدفعات:</strong> {{ $contract->installments_count ?? '-' }}</div>
        <div><strong>تاريخ البداية:</strong> {{ optional($contract->starts_on)->format('Y-m-d') ?? '-' }}</div>
        <div><strong>تاريخ النهاية:</strong> {{ optional($contract->ends_on)->format('Y-m-d') ?? '-' }}</div>
        <div><strong>الحالة:</strong> {{ $contract->status }}</div>
        <div><strong>ملاحظات:</strong> {{ $contract->notes ?: '-' }}</div>
    </div>

    <h3>ملخص مالي</h3>
    <table>
        <thead>
            <tr>
                <th>الإجمالي</th>
                <th>المدفوع</th>
                <th>المتبقي</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format((float) $contract->total_amount, 2) }}</td>
                <td>{{ number_format((float) $paidTotal, 2) }}</td>
                <td>{{ number_format((float) $remaining, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3 class="summary">الدفعات المجدولة</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>تاريخ الاستحقاق</th>
                <th>المبلغ</th>
                <th>المدفوع</th>
                <th>الحالة</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contract->installments as $installment)
                <tr>
                    <td>{{ $installment->installment_no }}</td>
                    <td>{{ optional($installment->due_date)->format('Y-m-d') }}</td>
                    <td>{{ number_format((float) $installment->amount, 2) }}</td>
                    <td>{{ number_format((float) $installment->paid_amount, 2) }}</td>
                    <td>{{ $installment->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد دفعات مجدولة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="summary">سجل الدفعات</h3>
    <table>
        <thead>
            <tr>
                <th>رقم الوصل</th>
                <th>التاريخ</th>
                <th>المبلغ</th>
                <th>طريقة الدفع</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contract->payments as $payment)
                <tr>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ optional($payment->paid_on)->format('Y-m-d') }}</td>
                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                    <td>{{ $payment->payment_method }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">لا توجد دفعات مسجلة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(empty($isPdf))
        <div class="actions">
            <button class="btn" onclick="window.print()">طباعة</button>
            <a class="btn" href="{{ route('accounting.contracts.download', $contract) }}">تنزيل PDF</a>
            <a class="btn" href="{{ route('accounting.contracts.index') }}">رجوع</a>
        </div>
    @endif
</body>
</html>
