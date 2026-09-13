@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
    $statusLabels = ['present' => trans('print.present'), 'late' => trans('print.late'), 'absent' => trans('print.absent')];
    $statusClasses = ['present' => 'pr-present', 'late' => 'pr-late', 'absent' => 'pr-absent'];
    $meta = [];
    if ($from || $to) {
        $meta[] = ['label' => trans('print.period'), 'value' => ($from ?: '…') . '  →  ' . ($to ?: '…')];
    }
    $meta[] = ['label' => trans('print.total_days'), 'value' => $rows->count()];
@endphp

<x-print-report
    :title="trans('main_sidebar.Absences')"
    :reportTitle="trans('main_sidebar.Absences')"
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
                    <th>{{ $shape(trans('print.date')) }}</th>
                    <th>{{ $shape(trans('print.student')) }}</th>
                    <th>{{ $shape(trans('print.section')) }}</th>
                    <th>{{ $shape(trans('print.present')) }}</th>
                    <th>{{ $shape(trans('print.late')) }}</th>
                    <th>{{ $shape(trans('print.absent')) }}</th>
                    <th>{{ $shape(trans('print.status')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td dir="ltr">{{ $row['date'] }}</td>
                        <td>{{ $shape($row['student']) }}</td>
                        <td>{{ $shape($row['section']) }}</td>
                        <td>{{ $row['present'] }}</td>
                        <td>{{ $row['late'] }}</td>
                        <td>{{ $row['absent'] }}</td>
                        <td><span class="pr-badge {{ $statusClasses[$row['status']] }}">{{ $shape($statusLabels[$row['status']]) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
