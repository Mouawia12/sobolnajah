@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
    $meta = [];
    if ($from || $to) {
        $meta[] = ['label' => trans('print.period'), 'value' => ($from ?: '…') . '  →  ' . ($to ?: '…')];
    }
    $meta[] = ['label' => trans('accounting.breadcrumbs.payments'), 'value' => $payments->count()];
    $meta[] = ['label' => trans('print.summary'), 'value' => number_format((float) $total, 2)];
@endphp

<x-print-report
    :title="trans('accounting.breadcrumbs.payments')"
    :reportTitle="trans('accounting.breadcrumbs.payments')"
    :schoolName="$schoolName"
    :meta="$meta"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($payments->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('accounting.payments_page.receipt_number')) }}</th>
                    <th>{{ $shape(trans('print.date')) }}</th>
                    <th>{{ $shape(trans('print.student')) }}</th>
                    <th>{{ $shape(trans('print.summary')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td dir="ltr">{{ $p->receipt_number }}</td>
                        <td dir="ltr">{{ $p->paid_on }}</td>
                        <td>{{ $shape(optional(optional($p->contract)->student?->user)->name ?? '—') }}</td>
                        <td dir="ltr">{{ number_format((float) $p->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="text-align:{{ App::isLocale('ar') ? 'left' : 'right' }};font-weight:800;">{{ $shape(trans('print.summary')) }}</td>
                    <td dir="ltr" style="font-weight:800;">{{ number_format((float) $total, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</x-print-report>
