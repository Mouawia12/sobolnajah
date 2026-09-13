@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);

    $sectionLabel = trim(
        (optional(optional($section->classroom)->schoolgrade)->name_grade ?? '') . ' / ' .
        (optional($section->classroom)->name_class ?? '') . ' / ' . ($section->name_section ?? ''), ' /'
    );

    $meta = [['label' => trans('academic.section'), 'value' => $sectionLabel]];
    if ($specialization) {
        $meta[] = ['label' => trans('academic.subject'), 'value' => (string) $specialization->name];
    }
@endphp

<x-print-report
    :title="trans('academic.results_sheet')"
    :reportTitle="trans('academic.results_sheet')"
    :schoolName="$schoolName"
    :meta="$meta"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null"
    :landscape="true">

    @if ($rows->isEmpty() || $assessments->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('academic.no_assessments')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:32px;">{{ $shape(trans('print.seq')) }}</th>
                    <th style="text-align:{{ App::isLocale('ar') ? 'right' : 'left' }};">{{ $shape(trans('academic.student')) }}</th>
                    @foreach ($assessments as $a)
                        <th>{{ $shape($a->title) }}<br><small>/{{ (int) $a->max_mark }}@if($a->coefficient != 1) · م{{ (float) $a->coefficient }}@endif</small></th>
                    @endforeach
                    <th>{{ $shape(trans('academic.average')) }}<br><small>{{ $shape(trans('academic.out_of_20')) }}</small></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="text-align:{{ App::isLocale('ar') ? 'right' : 'left' }};">{{ $shape($row['name']) }}</td>
                        @foreach ($assessments as $a)
                            <td dir="ltr">{{ $shape($row['cells'][$a->id] ?? '—') }}</td>
                        @endforeach
                        <td dir="ltr">
                            @if($row['average'] !== null)
                                <span class="pr-badge {{ $row['average'] >= 10 ? 'pr-present' : 'pr-absent' }}">{{ number_format($row['average'], 2) }}</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
