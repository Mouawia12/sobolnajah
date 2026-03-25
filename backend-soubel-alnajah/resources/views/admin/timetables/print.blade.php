<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>{{ trans('timetable.print') }}</title>
    <style>
        :root {
            --line: #d6deea;
            --text: #1f2a37;
            --muted: #5a6575;
            --head: #eef4fb;
            --surface: #ffffff;
            --sheet: #f8fbff;
        }
        @page { size: A4 landscape; margin: 6mm; }
        body {
            font-family: "DejaVu Sans", Tahoma, sans-serif;
            margin: 0;
            background: #fff;
            color: var(--text);
            font-size: 10.5px;
            line-height: 1.15;
        }
        .print-actions { margin-bottom: 8px; }
        .print-actions button {
            border: 1px solid #1f6fbe;
            background: #1f6fbe;
            color: #fff;
            border-radius: 8px;
            padding: 8px 14px;
            font-weight: 700;
            cursor: pointer;
        }
        .sheet {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
        }
        .header {
            margin-bottom: 8px;
            padding: 8px 10px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--sheet);
        }
        .header-layout {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .header-layout td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .header-logo {
            width: 82px;
            text-align: center;
        }
        .header-logo img {
            width: 54px;
            height: 54px;
            object-fit: contain;
        }
        .header-center {
            width: 235px;
            padding: 0 10px;
            text-align: center;
        }
        .header-title {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.15;
        }
        .header-subtitle {
            margin-top: 3px;
            font-size: 9px;
            color: var(--muted);
        }
        .header-meta-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 6px;
        }
        .header-meta-table td {
            border: none;
            padding: 0 0 0 6px;
            vertical-align: top;
        }
        .header-meta-table td:last-child {
            padding-left: 0;
        }
        .meta-card {
            padding: 6px 8px;
            background: #fff;
            border: 1px solid #dbe5f0;
            border-radius: 8px;
        }
        .meta-label {
            display: block;
            margin-bottom: 2px;
            font-size: 9px;
            font-weight: 700;
            color: var(--muted);
        }
        .meta-value {
            display: block;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        .schedule-table th,
        .schedule-table td {
            border: 1px solid var(--line);
            padding: 3px 4px;
            text-align: center;
            font-size: 9.5px;
            vertical-align: middle;
        }
        .schedule-table th {
            background: var(--head);
            font-weight: 700;
        }
        .schedule-table thead th {
            font-size: 9px;
            line-height: 1.05;
        }
        .schedule-table td {
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        @media print {
            .print-actions { display: none; }
            body { margin: 0; background: #fff; }
            .sheet {
                border: none;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
@php
    $schoolName = $timetable->section->school->name_school ?? 'Sobol Najah';
    $gradeName = $timetable->section->classroom->schoolgrade->name_grade ?? '—';
    $className = $timetable->section->classroom->name_class ?? '—';
    $sectionName = $timetable->section->name_section ?? '—';
    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;
@endphp
<div class="print-actions">
    <button onclick="window.print()">{{ trans('timetable.print') }}</button>
</div>
<div class="sheet">
    <div class="header">
        <table class="header-layout">
            <tr>
                <td>
                    <table class="header-meta-table">
                        <tr>
                            <td>
                                <div class="meta-card">
                                    <span class="meta-label">المؤسسة</span>
                                    <span class="meta-value">{{ $schoolName }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="meta-card">
                                    <span class="meta-label">{{ trans('timetable.school_year') }}</span>
                                    <span class="meta-value">{{ $timetable->academic_year }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="meta-card">
                                    <span class="meta-label">المستوى / القسم</span>
                                    <span class="meta-value">{{ $gradeName }} / {{ $className }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="meta-card">
                                    <span class="meta-label">الفوج</span>
                                    <span class="meta-value">{{ $sectionName }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="header-center">
                    <div class="header-title">{{ $timetable->title ?: trans('timetable.default_print_title') }}</div>
                    <div class="header-subtitle">{{ trans('timetable.print') }}</div>
                </td>
                <td class="header-logo">
                    @if($logoSrc)
                        <img src="{{ $logoSrc }}" alt="School Logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <table class="schedule-table">
        <thead>
        <tr>
            <th>{{ trans('timetable.day') }}</th>
            <th>{{ trans('timetable.period') }}</th>
            <th>{{ trans('timetable.from') }} - {{ trans('timetable.to') }}</th>
            <th>{{ trans('timetable.subject') }}</th>
            <th>{{ trans('timetable.teacher') }}</th>
            <th>{{ trans('timetable.room') }}</th>
        </tr>
        </thead>
        <tbody>
        @forelse($timetable->entries as $entry)
            <tr>
                <td>{{ $entry->day_of_week }}</td>
                <td>{{ $entry->period_index }}</td>
                <td>{{ $entry->starts_at ?: '-' }} - {{ $entry->ends_at ?: '-' }}</td>
                <td>{{ $entry->subject_name }}</td>
                <td>{{ optional($entry->teacher)->name ?? '-' }}</td>
                <td>{{ $entry->room_name ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6">{{ trans('timetable.no_entries') }}</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
