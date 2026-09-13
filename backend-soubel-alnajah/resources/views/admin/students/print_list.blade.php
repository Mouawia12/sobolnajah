@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="trans('print.students_list')"
    :reportTitle="trans('print.students_list')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('hr.employees'), 'value' => $students->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($students->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('print.student')) }}</th>
                    <th>{{ $shape(trans('print.national_id')) }}</th>
                    <th>{{ $shape(trans('inscription.niveau')) }}</th>
                    <th>{{ $shape(trans('inscription.Anneescolaire')) }}</th>
                    <th>{{ $shape(trans('print.section')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $i => $s)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape(trim(($s->prenom ?? '') . ' ' . ($s->nom ?? ''))) }}</td>
                        <td dir="ltr">{{ $s->national_id ?: '—' }}</td>
                        <td>{{ $shape($s->section?->classroom?->schoolgrade?->name_grade ?? '—') }}</td>
                        <td>{{ $shape($s->section?->classroom?->name_class ?? '—') }}</td>
                        <td>{{ $shape($s->section?->name_section ?? '—') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
