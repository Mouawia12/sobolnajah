@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="trans('main_sidebar.addclasse')"
    :reportTitle="trans('main_sidebar.addclasse')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('inscription.niveau'), 'value' => $grades->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($grades->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('inscription.niveau')) }}</th>
                    <th>{{ $shape(trans('inscription.ecole')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($grades as $i => $grade)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape($grade->name_grade) }}</td>
                        <td>{{ $shape(optional($grade->school)->name_school ?? '—') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
