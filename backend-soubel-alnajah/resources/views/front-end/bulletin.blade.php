@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);

    $section = $student->section;
    $sectionLabel = trim(
        (optional(optional($section)->classroom)->schoolgrade->name_grade ?? '') . ' / ' .
        (optional(optional($section)->classroom)->name_class ?? '') . ' / ' .
        (optional($section)->name_section ?? ''), ' /'
    );

    $typeLabels = ['devoir' => trans('academic.type_devoir'), 'exam' => trans('academic.type_exam')];

    $meta = [
        ['label' => trans('print.student'), 'value' => trim(($student->prenom ?? '') . ' ' . ($student->nom ?? ''))],
        ['label' => trans('print.section'), 'value' => $sectionLabel ?: '—'],
    ];
    if (!empty($term)) {
        $meta[] = ['label' => trans('academic.term'), 'value' => trans('academic.term_' . $term)];
    }
@endphp

<x-print-report
    :title="trans('academic.results_sheet')"
    :reportTitle="trans('academic.results_sheet')"
    :schoolName="$schoolName"
    :meta="$meta"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @unless($isPdf)
        <x-slot:actions>
            <form method="GET" style="display:flex;gap:6px;align-items:center;">
                <select name="term" onchange="this.form.submit()" style="padding:8px;border:1px solid #cdd7e2;border-radius:8px;">
                    <option value="">{{ trans('academic.all_terms') }}</option>
                    @foreach ([1,2,3] as $t)
                        <option value="{{ $t }}" @selected((string) $term === (string) $t)>{{ trans('academic.term_' . $t) }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:actions>
    @endunless

    @if (empty($bulletin['subjects']))
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('academic.no_assessments')) }}</p>
    @else
        @foreach ($bulletin['subjects'] as $subject)
            <h3 style="font-size:14px;color:#12355b;margin:14px 0 6px;border-right:4px solid #1f6fbe;padding-right:8px;">
                {{ $shape($subject['subject']) }}
            </h3>
            <table class="pr-table" style="margin-bottom:6px;">
                <thead>
                    <tr>
                        <th style="text-align:{{ App::isLocale('ar') ? 'right' : 'left' }};">{{ $shape(trans('academic.title')) }}</th>
                        <th>{{ $shape(trans('academic.type')) }}</th>
                        <th>{{ $shape(trans('academic.mark')) }}</th>
                        <th>{{ $shape(trans('academic.coefficient')) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subject['items'] as $item)
                        <tr>
                            <td style="text-align:{{ App::isLocale('ar') ? 'right' : 'left' }};">{{ $shape($item['title']) }}</td>
                            <td>{{ $shape($typeLabels[$item['type']] ?? $item['type']) }}</td>
                            <td dir="ltr">
                                @if ($item['absent'])
                                    <span class="pr-badge pr-absent">{{ $shape(trans('academic.absent')) }}</span>
                                @elseif ($item['mark'] !== null)
                                    {{ number_format($item['mark'], 2) }} / {{ (int) $item['max'] }}
                                @else
                                    —
                                @endif
                            </td>
                            <td dir="ltr">{{ (float) $item['coefficient'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3" style="text-align:{{ App::isLocale('ar') ? 'left' : 'right' }};font-weight:800;">{{ $shape(trans('academic.average')) }} ({{ $shape(trans('academic.out_of_20')) }})</td>
                        <td dir="ltr" style="font-weight:800;">
                            @if ($subject['average'] !== null)
                                <span class="pr-badge {{ $subject['average'] >= 10 ? 'pr-present' : 'pr-absent' }}">{{ number_format($subject['average'], 2) }}</span>
                            @else — @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <div class="pr-meta" style="margin-top:16px;">
            <div class="pr-meta-card" style="background:{{ ($bulletin['general_average'] ?? 0) >= 10 ? '#e6f4ec' : '#fbe9e6' }};">
                <span class="pr-meta-label">{{ $shape(trans('academic.general_average')) }} ({{ $shape(trans('academic.out_of_20')) }})</span>
                <span class="pr-meta-value" dir="ltr" style="font-size:18px;">
                    {{ $bulletin['general_average'] !== null ? number_format($bulletin['general_average'], 2) : '—' }}
                </span>
            </div>
        </div>
    @endif
</x-print-report>
