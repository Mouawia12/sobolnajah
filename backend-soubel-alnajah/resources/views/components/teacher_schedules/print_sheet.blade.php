<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ App::isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ trans('teacher_schedule.title') }}</title>
    <style>
        body { font-family: DejaVu Sans, Tahoma, sans-serif; margin: 18px; color:#1f2a37; }
        .header { text-align:center; margin-bottom:12px; }
        .header .title { font-size:20px; font-weight:700; }
        .meta { margin-top:8px; font-size:13px; color:#475569; line-height:1.8; }
        table { width:100%; border-collapse:collapse; }
        th,td { border:1px solid #d8e0ea; padding:6px; font-size:12px; vertical-align:top; }
        th { background:#eef4fb; }
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
        $schoolName = $schedule->school ? $schedule->school->getTranslation('name_school', app()->getLocale()) : 'Sobol Najah';
    @endphp
    <div>{{ trans('teacher_schedule.institution') }}: {{ $schedule->branch_name ?: $schoolName }}</div>
    <div class="title">{{ $schedule->title ?: trans('teacher_schedule.title') }}</div>
    <div class="meta">
        {{ trans('teacher_schedule.teacher') }}: {{ $schedule->teacher?->name ?? '—' }}<br>
        {{ trans('teacher_schedule.academic_year') }}: {{ $schedule->academic_year }}<br>
        {{ trans('teacher_schedule.approved_at') }}: {{ optional($schedule->approved_at)->format('Y-m-d') ?: '—' }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th style="width:95px">{{ trans('teacher_schedule.slot') }}</th>
            @foreach($schedule->slots as $slot)
                <th>
                    <div>{{ $slot->label ?: ('#' . $slot->slot_index) }}</div>
                    <small>{{ $slot->starts_at ?: '--:--' }} - {{ $slot->ends_at ?: '--:--' }}</small>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($days as $dayIndex => $dayLabel)
            <tr>
                <th>{{ $dayLabel }}</th>
                @foreach($schedule->slots as $slot)
                    @php($cell = $matrix[$dayIndex][$slot->slot_index] ?? null)
                    <td>
                        @if($cell)
                            <div class="cell-subject">{{ $cell['subject_name'] ?: '—' }}</div>
                            <div>{{ trans('teacher_schedule.class_name') }}: {{ $cell['class_name'] ?: '—' }}</div>
                            <div>{{ trans('teacher_schedule.room') }}: {{ $cell['room_name'] ?: '—' }}</div>
                            @if(!empty($cell['note']))
                                <div>{{ $cell['note'] }}</div>
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
    <div>{{ now()->format('Y-m-d') }}</div>
    <div>{{ $schedule->signature_text ?: trans('teacher_schedule.signature_text') }}</div>
</div>
</body>
</html>
