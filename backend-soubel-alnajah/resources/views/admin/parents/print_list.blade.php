@php
    $isPdf = $isPdf ?? false;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape((string) $value);
@endphp

<x-print-report
    :title="__('أولياء الأمور')"
    :reportTitle="__('أولياء الأمور')"
    :schoolName="$schoolName"
    :meta="[['label' => __('أولياء الأمور'), 'value' => $parents->count()]]"
    :isPdf="$isPdf"
    :pdfUrl="$pdfUrl ?? null">

    @if ($parents->isEmpty())
        <p style="text-align:center;color:#64748b;padding:24px;">{{ $shape(trans('print.no_records')) }}</p>
    @else
        <table class="pr-table">
            <thead>
                <tr>
                    <th style="width:36px;">{{ $shape(trans('print.seq')) }}</th>
                    <th>{{ $shape(__('الولي')) }}</th>
                    <th>{{ $shape(__('صلة القرابة')) }}</th>
                    <th>{{ $shape(trans('print.phone')) }}</th>
                    <th>{{ $shape(__('عدد الأبناء')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($parents as $i => $parent)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $shape(trim(($parent->prenomwali ?? '') . ' ' . ($parent->nomwali ?? ''))) }}</td>
                        <td>{{ $shape($parent->relationetudiant ?: '—') }}</td>
                        <td dir="ltr">{{ $parent->numtelephonewali ? '0' . $parent->numtelephonewali : '—' }}</td>
                        <td>{{ $parent->students_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-print-report>
