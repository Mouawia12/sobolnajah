@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="trans('main_sidebar.addecoles')"
    :reportTitle="trans('main_sidebar.addecoles')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('inscription.ecole'), 'value' => $schools->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($schools->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('inscription.ecole')) }}</th>
                    <th>{{ $shape(trans('inscription.niveau')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schools as $i => $school)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape($school->name_school) }}</td>
                        <td>{{ $school->schoolgrades_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
