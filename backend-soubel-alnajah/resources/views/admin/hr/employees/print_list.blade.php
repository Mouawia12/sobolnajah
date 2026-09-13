@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="trans('print.employees_list')"
    :reportTitle="trans('print.employees_list')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('hr.employees'), 'value' => $employees->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($employees->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('hr.name')) }}</th>
                    <th>{{ $shape(trans('print.job_title')) }}</th>
                    <th>{{ $shape(trans('print.phone')) }}</th>
                    <th>{{ $shape(trans('hr.joining_date')) }}</th>
                    <th>{{ $shape(trans('hr.is_active')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $i => $e)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape($e->name) }}</td>
                        <td>{{ $shape($e->job_title ?: '—') }}</td>
                        <td dir="ltr">{{ $e->phone ?: '—' }}</td>
                        <td dir="ltr">{{ $e->joining_date?->format('Y-m-d') ?: '—' }}</td>
                        <td>{{ $shape($e->is_active ? trans('hr.active') : trans('hr.inactive')) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
