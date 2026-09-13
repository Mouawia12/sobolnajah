@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="trans('print.teachers_list')"
    :reportTitle="trans('print.teachers_list')"
    :schoolName="$schoolName"
    :meta="[['label' => trans('teacher.teacher'), 'value' => $teachers->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($teachers->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(trans('hr.name')) }}</th>
                    <th>{{ $shape(trans('teacher.specialization')) }}</th>
                    <th>{{ $shape(trans('hr.email')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($teachers as $i => $t)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape($t->name) }}</td>
                        <td>{{ $shape(optional($t->specialization)->name ?? '—') }}</td>
                        <td dir="ltr">{{ $t->user?->email ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
