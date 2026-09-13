@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);

    $section = $student->section;
    $schoolName = $section?->classroom?->schoolgrade?->school?->name_school ?: trans('print.system_name');
    $sectionLabel = trim(
        ($section?->classroom?->schoolgrade?->name_grade ?? '') . ' / ' .
        ($section?->classroom?->name_class ?? '') . ' / ' .
        ($section?->name_section ?? ''), ' /'
    );

    $statusLabels = [
        'present' => trans('print.present'),
        'late' => trans('print.late'),
        'absent' => trans('print.absent'),
    ];
    $statusClasses = ['present' => 'pr-present', 'late' => 'pr-late', 'absent' => 'pr-absent'];

    $meta = [
        ['label' => trans('print.student'), 'value' => trim(($student->prenom ?? '') . ' ' . ($student->nom ?? ''))],
        ['label' => trans('print.national_id'), 'value' => $student->national_id ?: '—'],
        ['label' => trans('print.section'), 'value' => $sectionLabel ?: '—'],
        ['label' => trans('print.period'), 'value' => $from . '  →  ' . $to],
    ];
@endphp

<x-print-report
    :title="trans('print.student_attendance_report')"
    :reportTitle="trans('print.student_attendance_report')"
    :schoolName="$schoolName"
    :meta="$meta"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @unless($isPdf)
        <x-slot:actions>
            <form method="GET" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                <input type="date" name="date_from" value="{{ $from }}" style="padding:7px;border:1px solid #cdd7e2;border-radius:8px;">
                <input type="date" name="date_to" value="{{ $to }}" style="padding:7px;border:1px solid #cdd7e2;border-radius:8px;">
                <button type="submit" style="padding:9px 16px;border:1px solid #2e9e5b;background:#2e9e5b;color:#fff;border-radius:8px;font-weight:700;cursor:pointer;">{{ trans('hr.filter') }}</button>
            </form>
        </x-slot:actions>
    @endunless

    @if (empty($rows))
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('print.date')) }}</th>
                    <th>{{ $shape(trans('print.day')) }}</th>
                    <th>{{ $shape(trans('print.present')) }}</th>
                    <th>{{ $shape(trans('print.late')) }}</th>
                    <th>{{ $shape(trans('print.absent')) }}</th>
                    <th>{{ $shape(trans('print.status')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    @php $dayName = \Illuminate\Support\Carbon::parse($row['date'])->locale(app()->getLocale())->translatedFormat('l'); @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td dir="ltr">{{ $row['date'] }}</td>
                        <td>{{ $shape($dayName) }}</td>
                        <td>{{ $row['present'] }}</td>
                        <td>{{ $row['late'] }}</td>
                        <td>{{ $row['absent'] }}</td>
                        <td><span class="pr-badge {{ $statusClasses[$row['status']] }}">{{ $shape($statusLabels[$row['status']]) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pr-meta" style="margin-top:16px;">
            <div class="pr-meta-card pr-present" style="background:#e6f4ec;">
                <span class="pr-meta-label">{{ $shape(trans('print.total_present')) }}</span>
                <span class="pr-meta-value">{{ $summary['present'] }}</span>
            </div>
            <div class="pr-meta-card" style="background:#fdf3df;">
                <span class="pr-meta-label">{{ $shape(trans('print.total_late')) }}</span>
                <span class="pr-meta-value">{{ $summary['late'] }}</span>
            </div>
            <div class="pr-meta-card" style="background:#fbe9e6;">
                <span class="pr-meta-label">{{ $shape(trans('print.total_absent')) }}</span>
                <span class="pr-meta-value">{{ $summary['absent'] }}</span>
            </div>
            <div class="pr-meta-card">
                <span class="pr-meta-label">{{ $shape(trans('print.total_days')) }}</span>
                <span class="pr-meta-value">{{ $summary['present'] + $summary['late'] + $summary['absent'] }}</span>
            </div>
        </div>
    @endif
</x-print-report>
