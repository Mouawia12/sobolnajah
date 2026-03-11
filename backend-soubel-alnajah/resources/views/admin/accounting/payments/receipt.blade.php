@extends('layoutsadmin.masteradmin')
@section('titlea', 'وصل الدفع')

@section('contenta')
@php
    $contract = $payment->contract;
    $student = $contract?->student;
    $parent = $student?->parent;
    $school = $student?->section?->school;

    $pickTranslation = static function ($value): string {
        if ($value === null) {
            return '';
        }

        if (is_array($value)) {
            foreach (['ar', 'fr', 'en'] as $lang) {
                if (!empty($value[$lang])) {
                    return trim((string) $value[$lang]);
                }
            }

            foreach ($value as $item) {
                if (!empty($item)) {
                    return trim((string) $item);
                }
            }

            return '';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                foreach (['ar', 'fr', 'en'] as $lang) {
                    if (!empty($decoded[$lang])) {
                        return trim((string) $decoded[$lang]);
                    }
                }

                foreach ($decoded as $item) {
                    if (!empty($item)) {
                        return trim((string) $item);
                    }
                }
            }

            return trim($value);
        }

        return trim((string) $value);
    };

    $guardianName = trim((string) ($contract?->guardian_name ?? ''));
    if ($guardianName === '') {
        $guardianName = trim(
            $pickTranslation($parent?->prenomwali ?? '') . ' ' . $pickTranslation($parent?->nomwali ?? '')
        );
    }
    if ($guardianName === '') {
        $guardianName = $pickTranslation($parent?->user?->name ?? '');
    }
    if ($guardianName === '') {
        $guardianName = '-';
    }

    $receiptNumber = (string) ($payment->receipt_number ?: ($payment->receipt?->receipt_code ?: $payment->id));
    $receiptDate = optional($payment->paid_on)->format('d/m/Y') ?: optional($payment->created_at)->format('d/m/Y');

    $contractTotal = (float) ($contract?->total_amount ?? 0);
    $currentPaidAmount = (float) $payment->amount;
    $paymentDateKey = optional($payment->paid_on)->format('Y-m-d') ?: optional($payment->created_at)->format('Y-m-d');

    $paidBeforeCurrent = collect($contract?->payments ?? [])->filter(
        static function ($contractPayment) use ($payment, $paymentDateKey): bool {
            if ((int) $contractPayment->id === (int) $payment->id) {
                return false;
            }

            $candidateDateKey = optional($contractPayment->paid_on)->format('Y-m-d') ?: optional($contractPayment->created_at)->format('Y-m-d');

            if ($candidateDateKey < $paymentDateKey) {
                return true;
            }

            return $candidateDateKey === $paymentDateKey && (int) $contractPayment->id < (int) $payment->id;
        }
    )->sum(static fn ($contractPayment): float => (float) $contractPayment->amount);

    $oldBalance = max($contractTotal - (float) $paidBeforeCurrent, 0);
    $remainingBalance = max($oldBalance - $currentPaidAmount, 0);

    $schoolName = $pickTranslation($school?->name_school ?? '') ?: 'مدرسة سبل النجاح الخاصة';
    $schoolAddress = 'حي الرمال ولاية الوادي';
    $schoolPhones = '0542454226/0663154663';
    $formattedPaid = number_format($currentPaidAmount, 2);
@endphp

<div class="receipt-toolbar admin-print-hide">
    <button class="btn btn-sm btn-info" onclick="window.print()">طباعة</button>
</div>

<div class="receipt-page">
    <div class="receipt-sheet">
        <div class="receipt-header">
            <div class="receipt-logo-wrap">
                <img src="{{ asset('images/receipt-template-logo.jpeg') }}" alt="School Logo" class="receipt-logo">
            </div>
            <div class="receipt-school-info">
                <h2>{{ $schoolName }}</h2>
                <p>{{ $schoolAddress }}</p>
                <p class="receipt-school-phone">{{ $schoolPhones }}</p>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <div class="receipt-line"><strong>اسم ولقب والي التلميذ:</strong> {{ $guardianName }}</div>

        <div class="receipt-meta">
            <div><strong>رقم الوصل :</strong> <span class="ltr">{{ $receiptNumber }}</span></div>
            <div><strong>تاريخ :</strong> <span class="ltr">{{ $receiptDate ?: '-' }}</span></div>
        </div>

        <table class="receipt-table">
            <thead>
                <tr>
                    <th class="col-no">رقم</th>
                    <th class="col-desc">تعيين</th>
                    <th class="col-amount">مبلغ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-no">1</td>
                    <td class="col-desc">تلميذ الاول</td>
                    <td class="col-amount">&nbsp;</td>
                </tr>
                <tr>
                    <td class="col-no">2</td>
                    <td class="col-desc">اخوه 2 ان وجد</td>
                    <td class="col-amount">&nbsp;</td>
                </tr>
                <tr>
                    <td class="col-no">3</td>
                    <td class="col-desc">اخوه 3 ان وجد</td>
                    <td class="col-amount">&nbsp;</td>
                </tr>
                <tr>
                    <td class="col-no">4</td>
                    <td class="col-desc">اخوه 4 ان وجد</td>
                    <td class="col-amount">&nbsp;</td>
                </tr>
                <tr>
                    <td class="col-no">&nbsp;</td>
                    <td class="col-desc">مبلغ المدفوع</td>
                    <td class="col-amount ltr">{{ number_format($currentPaidAmount, 2) }}</td>
                </tr>
                <tr>
                    <td class="col-no">&nbsp;</td>
                    <td class="col-desc">رصيد قديم</td>
                    <td class="col-amount ltr">{{ number_format($oldBalance, 2) }}</td>
                </tr>
                <tr>
                    <td class="col-no">&nbsp;</td>
                    <td class="col-desc">باقي الرصيد</td>
                    <td class="col-amount ltr">{{ number_format($remainingBalance, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="receipt-final-line">
            أوقف هذا الحساب على مبلغ اجمالي قدره:
            <span class="receipt-final-amount ltr">{{ $formattedPaid }}</span>
        </div>

        <div class="receipt-signature">امضاء</div>
    </div>
</div>

<style>
@page {
    size: A5 portrait;
    margin: 0;
}

.receipt-toolbar {
    margin-bottom: 12px;
}

.receipt-page {
    width: 100%;
    display: flex;
    justify-content: center;
}

.receipt-sheet {
    width: 148mm;
    min-height: 210mm;
    background: #ffffff;
    color: #111111;
    padding: 12mm 12mm 20mm;
    border: none;
    font-family: "Times New Roman", "Amiri", "DejaVu Sans", serif;
    font-size: 5.6mm;
    line-height: 1.2;
    direction: rtl;
    box-sizing: border-box;
}

.receipt-header {
    display: flex;
    align-items: center;
    gap: 5mm;
    direction: ltr;
}

.receipt-logo-wrap {
    width: 36.8mm;
    flex: 0 0 36.8mm;
}

.receipt-logo {
    width: 100%;
    height: auto;
    display: block;
}

.receipt-school-info {
    flex: 1;
    text-align: center;
    direction: rtl;
}

.receipt-school-info h2 {
    margin: 0;
    font-size: 7.1mm;
    font-weight: 700;
}

.receipt-school-info p {
    margin: 0.8mm 0 0;
    font-size: 5.6mm;
    font-weight: 600;
}

.receipt-school-phone {
    font-size: 5.4mm !important;
}

.receipt-divider {
    border-top: 0.5mm solid #171717;
    margin: 3.2mm 0 5mm;
}

.receipt-line {
    font-size: 5.6mm;
    margin-bottom: 4.4mm;
}

.receipt-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 5.6mm;
    margin-bottom: 4mm;
}

.receipt-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 5.6mm;
}

.receipt-table th,
.receipt-table td {
    border: 1px solid #8e8e8e;
    padding: 1.6mm 1.9mm;
    height: 8.5mm;
    line-height: 1.2;
}

.receipt-table th {
    font-weight: 700;
}

.col-no {
    width: 9.5%;
    text-align: center;
}

.col-desc {
    width: 57%;
    text-align: right;
}

.col-amount {
    width: 33.5%;
    text-align: center;
}

.receipt-final-line {
    margin-top: 9mm;
    font-size: 5.6mm;
    text-align: right;
}

.receipt-final-amount {
    display: inline-block;
    min-width: 28mm;
    border-bottom: 1px solid #222;
    text-align: center;
    margin-right: 1.5mm;
}

.receipt-signature {
    margin-top: 16mm;
    margin-right: auto;
    margin-left: 0;
    font-size: 5.6mm;
    text-align: left;
    text-decoration: underline;
    width: fit-content;
}

.ltr {
    direction: ltr;
    unicode-bidi: bidi-override;
    display: inline-block;
}

@media print {
    html,
    body {
        background: #ffffff !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .admin-print-hide,
    .main-sidebar,
    .main-header,
    .content-header,
    .main-footer,
    .control-sidebar {
        display: none !important;
    }

    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }

    .receipt-page {
        display: block;
    }

    .receipt-sheet {
        margin: 0;
        border: none;
        width: 148mm;
        min-height: 210mm;
        padding: 12mm 12mm 20mm;
    }
}
</style>
@endsection
