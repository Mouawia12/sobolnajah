<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>طباعة العقود حسب التاريخ</title>
    <style>
        * { font-family: "DejaVu Sans", sans-serif; box-sizing: border-box; }
        body { direction: rtl; color: #111; margin: 20px; font-size: 13px; }
        h2, h3 { margin: 0 0 10px 0; }
        .meta { margin-bottom: 12px; }
        .ltr { direction: ltr; unicode-bidi: bidi-override; display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 7px; text-align: center; }
        th { background: #f3f3f3; }
        .actions { margin-top: 14px; display: flex; gap: 8px; }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #888;
            border-radius: 4px;
            text-decoration: none;
            color: #111;
            background: #fff;
        }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    @php
        $planLabels = ['yearly' => 'سنوي', 'monthly' => 'شهري', 'installments' => 'أقساط'];
        $statusLabels = ['draft' => 'مسودة', 'active' => 'نشط', 'partial' => 'جزئي', 'paid' => 'مدفوع', 'overdue' => 'متأخر', 'pending' => 'قيد الانتظار'];
    @endphp

    <h2>تقرير العقود حسب التاريخ</h2>
    <div class="meta">
        <div>من: <span class="ltr">{{ $fromDate->format('Y-m-d') }}</span></div>
        <div>إلى: <span class="ltr">{{ $toDate->format('Y-m-d') }}</span></div>
        <div>عدد العقود: <span class="ltr">{{ $contracts->count() }}</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>رقم العقد</th>
                <th>الطالب</th>
                <th>السنة</th>
                <th>الخطة</th>
                <th>الحالة</th>
                <th>الإجمالي</th>
                <th>المدفوع</th>
                <th>المتبقي</th>
                <th>تاريخ الإنشاء</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contracts as $index => $contract)
                @php
                    $paid = (float) ($contract->paid_total ?? 0);
                    $remaining = max(((float) $contract->total_amount - $paid), 0);
                @endphp
                <tr>
                    <td><span class="ltr">{{ $index + 1 }}</span></td>
                    <td><span class="ltr">{{ $contract->id }}</span></td>
                    <td>{{ $contract->student->user->name ?? ('Student #' . $contract->student_id) }}</td>
                    <td><span class="ltr">{{ $contract->academic_year }}</span></td>
                    <td>{{ $planLabels[$contract->plan_type] ?? $contract->plan_type }}</td>
                    <td>{{ $statusLabels[$contract->status] ?? $contract->status }}</td>
                    <td><span class="ltr">{{ number_format((float) $contract->total_amount, 2) }}</span></td>
                    <td><span class="ltr">{{ number_format($paid, 2) }}</span></td>
                    <td><span class="ltr">{{ number_format($remaining, 2) }}</span></td>
                    <td><span class="ltr">{{ optional($contract->created_at)->format('Y-m-d') }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">لا توجد عقود ضمن هذه الفترة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3 style="margin-top:12px;">الملخص</h3>
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
                <td><span class="ltr">{{ number_format((float) $totals['total_amount'], 2) }}</span></td>
                <td><span class="ltr">{{ number_format((float) $totals['paid_total'], 2) }}</span></td>
                <td><span class="ltr">{{ number_format((float) $totals['remaining'], 2) }}</span></td>
            </tr>
        </tbody>
    </table>

    <div class="actions">
        <button class="btn" onclick="window.print()">طباعة</button>
        <a class="btn" href="{{ route('accounting.contracts.index') }}">رجوع</a>
    </div>
</body>
</html>
