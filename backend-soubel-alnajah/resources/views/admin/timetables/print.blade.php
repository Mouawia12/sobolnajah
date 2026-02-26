<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>طباعة الجدول</title>
    <style>
        :root {
            --line: #d6deea;
            --text: #1f2a37;
            --muted: #5a6575;
            --head: #eef4fb;
            --surface: #ffffff;
        }
        body {
            font-family: Tahoma, sans-serif;
            margin: 24px;
            background: #f6f9ff;
            color: var(--text);
        }
        .print-actions { margin-bottom: 12px; }
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
            border-radius: 12px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 6px;
        }
        .title { font-size: 22px; font-weight: 700; }
        .meta {
            margin-top: 8px;
            color: var(--muted);
            line-height: 1.8;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            border: 1px solid var(--line);
        }
        th,
        td {
            border: 1px solid var(--line);
            padding: 9px 8px;
            text-align: center;
            font-size: 13px;
        }
        th {
            background: var(--head);
            font-weight: 700;
        }
        @media print {
            .print-actions { display: none; }
            body { margin: 8mm; background: #fff; }
            .sheet {
                border: none;
                border-radius: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div class="print-actions">
    <button onclick="window.print()">طباعة</button>
</div>
<div class="sheet">
    <div class="header">
        <div class="school-name">{{ $timetable->section->school->name_school ?? 'Sobol Najah' }}</div>
        <div class="title">{{ $timetable->title ?: 'الجدول الدراسي' }}</div>
        <div class="meta">
            {{ $timetable->section->classroom->schoolgrade->name_grade ?? '' }} /
            {{ $timetable->section->classroom->name_class ?? '' }} /
            {{ $timetable->section->name_section ?? '' }}<br>
            السنة الدراسية: {{ $timetable->academic_year }}
        </div>
    </div>
    <table>
        <thead>
        <tr>
            <th>اليوم</th>
            <th>الحصة</th>
            <th>التوقيت</th>
            <th>المادة</th>
            <th>الأستاذ</th>
            <th>القاعة</th>
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
                <td colspan="6">لا توجد حصص مسجلة في هذا الجدول.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
