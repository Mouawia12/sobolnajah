@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="trans('main_sidebar.addclasseroom')"
    :reportTitle="trans('main_sidebar.addclasseroom')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('inscription.Anneescolaire'), 'value' => $classrooms->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($classrooms->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('inscription.Anneescolaire')) }}</th>
                    <th>{{ $shape(trans('inscription.niveau')) }}</th>
                    <th>{{ $shape(trans('inscription.ecole')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classrooms as $i => $c)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape($c->name_class) }}</td>
                        <td>{{ $shape(optional($c->schoolgrade)->name_grade ?? '—') }}</td>
                        <td>{{ $shape(optional(optional($c->schoolgrade)->school)->name_school ?? '—') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
