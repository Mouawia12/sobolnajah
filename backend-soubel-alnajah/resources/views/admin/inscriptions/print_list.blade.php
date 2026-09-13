@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
    $statusLabels = [
        'procec' => trans('inscription.undefined'),
        'accept' => trans('inscription.accept'),
        'noaccept' => trans('inscription.noaccept'),
    ];
@endphp

<x-print-report
    :title="trans('inscription.studentinscription')"
    :reportTitle="trans('inscription.studentinscription')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('inscription.student'), 'value' => $inscriptions->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($inscriptions->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('inscription.student')) }}</th>
                    <th>{{ $shape(trans('print.phone')) }}</th>
                    <th>{{ $shape(trans('hr.email')) }}</th>
                    <th>{{ $shape(trans('inscription.status')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($inscriptions as $i => $ins)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape(trim(($ins->prenom ?? '') . ' ' . ($ins->nom ?? ''))) }}</td>
                        <td dir="ltr">{{ $ins->numtelephone ?: '—' }}</td>
                        <td dir="ltr">{{ $ins->email ?: '—' }}</td>
                        <td>{{ $shape($statusLabels[$ins->statu] ?? $ins->statu) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
