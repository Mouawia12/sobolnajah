<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ App::isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ trans('teacher_schedule.title') }}</title>
    <style>
        body { font-family: DejaVu Sans, Tahoma, sans-serif; margin: 18px; color:#1f2a37; }
        .header { margin-bottom:14px; padding:14px 18px; border:1px solid #d8e0ea; border-radius:14px; background:#f8fbff; }
        .header-layout { width:100%; border-collapse:collapse; table-layout:fixed; }
        .header-layout td { border:none; padding:0; vertical-align:middle; }
        .header-logo { width:110px; text-align:center; }
        .header-logo .logo-wrap { margin:0; }
        .header-logo img { width:74px; height:74px; object-fit:contain; }
        .header-center { width:280px; text-align:center; padding:0 18px; }
        .header-title { font-size:24px; font-weight:700; line-height:1.3; }
        .header-meta { width:auto; }
        .header-meta-table { width:100%; border-collapse:separate; border-spacing:0 10px; }
        .header-meta-table td { border:none; padding:0 0 0 10px; vertical-align:top; }
        .header-meta-table td:last-child { padding-left:0; }
        .meta-card { padding:9px 12px; background:#fff; border:1px solid #dbe5f0; border-radius:10px; }
        .meta-label { display:block; margin-bottom:3px; font-size:11px; font-weight:700; color:#64748b; }
        .meta-value { display:block; font-size:13px; font-weight:600; color:#1f2a37; }
        .schedule-table { width:100%; border-collapse:collapse; }
        .schedule-table th, .schedule-table td { border:1px solid #d8e0ea; padding:6px; font-size:12px; vertical-align:top; }
        .schedule-table th { background:#eef4fb; }
        .cell-subject { font-weight:700; }
        .print-actions { margin-bottom:12px; }
        .print-actions a, .print-actions button { padding:8px 12px; border:1px solid #1f6fbe; background:#1f6fbe; color:#fff; text-decoration:none; border-radius:8px; }
        @media print { .print-actions { display:none; } body{ margin:8mm; } }
    </style>
</head>
<body>
@isset($showActions)
    <div class="print-actions">
        <button onclick="window.print()">{{ trans('teacher_schedule.print') }}</button>
    </div>
@endisset

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
            <th style="width:95px">{{ $shape(trans('teacher_schedule.slot')) }}</th>
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

<div style="margin-top:16px; font-size:13px; display:flex; justify-content:space-between;">
    <div>{{ $shape(now()->format('Y-m-d')) }}</div>
    <div>{{ $shape($schedule->signature_text ?: trans('teacher_schedule.signature_text')) }}</div>
</div>
</body>
</html>
