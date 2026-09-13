@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);

    $meta = [
        ['label' => trans('print.period'), 'value' => $from . '  →  ' . $to],
    ];
@endphp

<x-print-report
    :title="trans('hr.attendance_report')"
    :reportTitle="trans('print.staff_attendance_report')"
    :schoolName="$schoolName"
    :meta="$meta"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($rows->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('print.staff_member')) }}</th>
                    <th>{{ $shape(trans('print.job_title')) }}</th>
                    <th>{{ $shape(trans('print.total_present')) }}</th>
                    <th>{{ $shape(trans('print.total_late')) }}</th>
                    <th>{{ $shape(trans('print.total_absent')) }}</th>
                    <th>{{ $shape(trans('print.total_days')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape($row['name']) }}</td>
                        <td>{{ $shape($row['subtitle']) }}</td>
                        <td><span class="pr-badge pr-present">{{ $row['present'] }}</span></td>
                        <td><span class="pr-badge pr-late">{{ $row['late'] }}</span></td>
                        <td><span class="pr-badge pr-absent">{{ $row['absent'] }}</span></td>
                        <td>{{ $row['total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
