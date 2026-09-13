@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="trans('main_sidebar.addsection')"
    :reportTitle="trans('main_sidebar.addsection')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('inscription.section'), 'value' => $sections->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($sections->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('inscription.section')) }}</th>
                    <th>{{ $shape(trans('inscription.Anneescolaire')) }}</th>
                    <th>{{ $shape(trans('inscription.niveau')) }}</th>
                    <th>{{ $shape(trans('hr.is_active')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sections as $i => $s)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape($s->name_section) }}</td>
                        <td>{{ $shape(optional($s->classroom)->name_class ?? '—') }}</td>
                        <td>{{ $shape(optional(optional($s->classroom)->schoolgrade)->name_grade ?? '—') }}</td>
                        <td>
                            @if ((int) $s->Status === 1)
                                <span class="pr-badge pr-present">{{ $shape(trans('hr.active')) }}</span>
                            @else
                                <span class="pr-badge pr-absent">{{ $shape(trans('hr.inactive')) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
