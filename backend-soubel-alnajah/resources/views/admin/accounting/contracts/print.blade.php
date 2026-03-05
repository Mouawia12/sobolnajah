<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>عقد رقم {{ $contract->id }}</title>
    <style>
        @page { margin: 20px; }
        * { font-family: "DejaVu Sans", sans-serif; box-sizing: border-box; }
        body {
            direction: rtl;
            unicode-bidi: plaintext;
            color: #111;
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
        }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        .header-right, .header-left {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .header-left {
            text-align: left;
        }
        .meta { margin-bottom: 12px; }
        .meta div { margin-bottom: 2px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 7px;
            text-align: center;
            vertical-align: middle;
        }
        th { background: #f3f3f3; }
        .section-title { margin-top: 14px; }
        .ltr {
            direction: ltr;
            unicode-bidi: bidi-override;
            display: inline-block;
        }
        .actions {
            margin-top: 16px;
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #888;
            text-decoration: none;
            color: #111;
            border-radius: 4px;
            background: #fff;
        }
        @media print {
            .actions { display: none; }
        }
    </style>
</head>
<body>
    @php
        $planLabels = [
            'yearly' => 'سنوي',
            'monthly' => 'شهري',
            'installments' => 'أقساط',
        ];
        $statusLabels = [
            'draft' => 'مسودة',
            'active' => 'نشط',
            'partial' => 'جزئي',
            'paid' => 'مدفوع',
            'overdue' => 'متأخر',
            'pending' => 'قيد الانتظار',
        ];
        $paymentMethodLabels = [
            'cash' => 'نقدي',
            'card' => 'بطاقة',
            'bank' => 'تحويل بنكي',
            'transfer' => 'تحويل',
            'check' => 'شيك',
        ];
    @endphp

    <div class="header">
        <div class="header-right">
            <h2>عقد طالب</h2>
            <div><strong>رقم العقد:</strong> {{ $contract->id }}</div>
        </div>
        <div class="header-left">
            <div><strong>تاريخ الطباعة:</strong> <span class="ltr">{{ now()->format('Y-m-d H:i') }}</span></div>
        </div>
    </div>

    <div class="meta">
        <div><strong>الطالب:</strong> {{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }}</div>
        <div><strong>البريد:</strong> <span class="ltr">{{ $contract->student->user->email ?? '-' }}</span></div>
        <div><strong>السنة الدراسية:</strong> <span class="ltr">{{ $contract->academic_year }}</span></div>
        <div><strong>نوع الخطة:</strong> {{ $planLabels[$contract->plan_type] ?? $contract->plan_type }}</div>
        <div><strong>عدد الدفعات:</strong> <span class="ltr">{{ $contract->installments_count ?? '-' }}</span></div>
        <div><strong>تاريخ البداية:</strong> <span class="ltr">{{ optional($contract->starts_on)->format('Y-m-d') ?? '-' }}</span></div>
        <div><strong>تاريخ النهاية:</strong> <span class="ltr">{{ optional($contract->ends_on)->format('Y-m-d') ?? '-' }}</span></div>
        <div><strong>الحالة:</strong> {{ $statusLabels[$contract->status] ?? $contract->status }}</div>
        <div><strong>ملاحظات:</strong> {{ $contract->notes ?: '-' }}</div>
    </div>

    <h3 class="section-title">ملخص مالي</h3>
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
                <td><span class="ltr">{{ number_format((float) $contract->total_amount, 2) }}</span></td>
                <td><span class="ltr">{{ number_format((float) $paidTotal, 2) }}</span></td>
                <td><span class="ltr">{{ number_format((float) $remaining, 2) }}</span></td>
            </tr>
        </tbody>
    </table>

    <h3 class="section-title">الدفعات المجدولة</h3>
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
                    <td><span class="ltr">{{ $installment->installment_no }}</span></td>
                    <td><span class="ltr">{{ optional($installment->due_date)->format('Y-m-d') }}</span></td>
                    <td><span class="ltr">{{ number_format((float) $installment->amount, 2) }}</span></td>
                    <td><span class="ltr">{{ number_format((float) $installment->paid_amount, 2) }}</span></td>
                    <td>{{ $statusLabels[$installment->status] ?? $installment->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد دفعات مجدولة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="section-title">سجل الدفعات</h3>
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
                    <td><span class="ltr">{{ $payment->receipt_number }}</span></td>
                    <td><span class="ltr">{{ optional($payment->paid_on)->format('Y-m-d') }}</span></td>
                    <td><span class="ltr">{{ number_format((float) $payment->amount, 2) }}</span></td>
                    <td>{{ $paymentMethodLabels[$payment->payment_method] ?? $payment->payment_method }}</td>
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
