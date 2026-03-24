<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ App::isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ trans('teacher_schedule.title') }}</title>
    <style>
        @page { size: A4 landscape; margin: 6mm; }
        body { font-family: DejaVu Sans, Tahoma, sans-serif; margin: 0; color:#1f2a37; font-size:10.5px; line-height:1.15; }
        .sheet-frame { width:100%; max-width:100%; }
        .header { margin-bottom:8px; padding:8px 12px; border:1px solid #d8e0ea; border-radius:10px; background:#f8fbff; }
        .header-layout { width:100%; border-collapse:collapse; table-layout:fixed; }
        .header-layout td { border:none; padding:0; vertical-align:middle; }
        .header-logo { width:82px; text-align:center; }
        .header-logo .logo-wrap { margin:0; }
        .header-logo img { width:54px; height:54px; object-fit:contain; }
        .header-center { width:235px; text-align:center; padding:0 10px; }
        .header-title { font-size:17px; font-weight:700; line-height:1.15; }
        .header-meta { width:auto; }
        .header-meta-table { width:100%; border-collapse:separate; border-spacing:0 6px; }
        .header-meta-table td { border:none; padding:0 0 0 6px; vertical-align:top; }
        .header-meta-table td:last-child { padding-left:0; }
        .meta-card { padding:6px 8px; background:#fff; border:1px solid #dbe5f0; border-radius:8px; }
        .meta-label { display:block; margin-bottom:2px; font-size:9px; font-weight:700; color:#64748b; }
        .meta-value { display:block; font-size:10.5px; font-weight:600; color:#1f2a37; white-space:nowrap; }
        .schedule-table { width:100%; border-collapse:collapse; table-layout:fixed; page-break-inside:avoid; }
        .schedule-table th, .schedule-table td { border:1px solid #d8e0ea; padding:3px 4px; font-size:9.5px; vertical-align:top; }
        .schedule-table th { background:#eef4fb; }
        .schedule-table thead th { font-size:9px; line-height:1.05; }
        .schedule-table tbody th { width:64px; font-size:10px; }
        .schedule-table small { display:block; margin-top:1px; font-size:8px; line-height:1; }
        .schedule-table tbody td { line-height:1.05; word-wrap:break-word; overflow-wrap:anywhere; }
        .schedule-table tbody td > div { margin-bottom:1px; }
        .schedule-table tbody td > div:last-child { margin-bottom:0; }
        .cell-subject { font-weight:700; font-size:10px; }
        .print-actions { margin-bottom:8px; }
        .print-actions a, .print-actions button { padding:8px 12px; border:1px solid #1f6fbe; background:#1f6fbe; color:#fff; text-decoration:none; border-radius:8px; }
        .sheet-footer { margin-top:8px; font-size:10px; display:flex; justify-content:space-between; }
        @media print {
            .print-actions { display:none; }
            body { margin: 0; }
            .sheet-frame { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
@isset($showActions)
    <div class="print-actions">
        <button onclick="window.print()">{{ trans('teacher_schedule.print') }}</button>
    </div>
@endisset

<div class="sheet-frame">
<div class="header">
    @php
        $isPdf = $isPdf ?? false;
        $schoolName = $schedule->school ? $schedule->school->getTranslation('name_school', app()->getLocale()) : 'Sobol Najah';
        $logoPath = public_path('images/logo.png');
        $logoSrc = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
        $pdfText = app(\App\Support\PdfArabicText::class, [
            'enabled' => $isPdf && App::isLocale('ar'),
        ]);
        $shape = fn ($value) => $pdfText->shape($value);
    @endphp
    <table class="header-layout">
        <tr>
            <td class="header-meta">
                <table class="header-meta-table">
                    <tr>
                        <td>
                            <div class="meta-card">
                                <span class="meta-label">{{ $shape(trans('teacher_schedule.institution')) }}</span>
                                <span class="meta-value">{{ $shape($schedule->branch_name ?: $schoolName) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="meta-card">
                                <span class="meta-label">{{ $shape(trans('teacher_schedule.teacher')) }}</span>
                                <span class="meta-value">{{ $shape($schedule->teacher?->name ?? '—') }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="meta-card">
                                <span class="meta-label">{{ $shape(trans('teacher_schedule.academic_year')) }}</span>
                                <span class="meta-value">{{ $shape($schedule->academic_year) }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="meta-card">
                                <span class="meta-label">{{ $shape(trans('teacher_schedule.approved_at')) }}</span>
                                <span class="meta-value">{{ $shape(optional($schedule->approved_at)->format('Y-m-d') ?: '—') }}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="header-center">
                <div class="header-title">{{ $shape($schedule->title ?: trans('teacher_schedule.title')) }}</div>
            </td>
            <td class="header-logo">
                @if($logoSrc)
                    <div class="logo-wrap">
                        <img src="{{ $logoSrc }}" alt="School Logo">
                    </div>
                @endif
            </td>
        </tr>
    </table>
</div>

<table class="schedule-table">
    <thead>
        <tr>
            <th style="width:64px">{{ $shape(trans('teacher_schedule.slot')) }}</th>
            @foreach($schedule->slots as $slot)
                <th>
                    <div>{{ $shape($slot->label ?: ('#' . $slot->slot_index)) }}</div>
                    <small>{{ $shape($slot->starts_at ?: '--:--') }} - {{ $shape($slot->ends_at ?: '--:--') }}</small>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($days as $dayIndex => $dayLabel)
            <tr>
                <th>{{ $shape($dayLabel) }}</th>
                @foreach($schedule->slots as $slot)
                    @php($cell = $matrix[$dayIndex][$slot->slot_index] ?? null)
                    <td>
                        @if($cell)
                            <div class="cell-subject">{{ $shape($cell['subject_name'] ?: '—') }}</div>
                            <div>{{ $shape(trans('teacher_schedule.class_name')) }}: {{ $shape($cell['class_name'] ?: '—') }}</div>
                            <div>{{ $shape(trans('teacher_schedule.room')) }}: {{ $shape($cell['room_name'] ?: '—') }}</div>
                            @if(!empty($cell['note']))
                                <div>{{ $shape($cell['note']) }}</div>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<div class="sheet-footer">
    <div>{{ $shape(now()->format('Y-m-d')) }}</div>
    <div>{{ $shape($schedule->signature_text ?: trans('teacher_schedule.signature_text')) }}</div>
</div>
</div>
</body>
</html>
