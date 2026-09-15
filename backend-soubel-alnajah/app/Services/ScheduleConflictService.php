<?php

namespace App\Services;

use App\Models\Inscription\Teacher;
use App\Models\School\Section;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * كشف تعارضات الحصص في جداول الأقسام (timetable_entries):
 *  - نفس الأستاذ محجوز في قسمين مختلفين بنفس اليوم والحصّة.
 *  - نفس القاعة محجوزة لقسمين مختلفين بنفس اليوم والحصّة.
 */
class ScheduleConflictService
{
    public function forSchool(?int $schoolId, ?string $academicYear = null): Collection
    {
        $entries = DB::table('timetable_entries as e')
            ->join('timetables as t', 't.id', '=', 'e.timetable_id')
            ->when($schoolId, fn ($q) => $q->where('t.school_id', $schoolId))
            ->when($academicYear, fn ($q) => $q->where('t.academic_year', $academicYear))
            ->select([
                'e.teacher_id', 'e.day_of_week', 'e.period_index', 'e.subject_name',
                'e.room_name', 'e.starts_at', 'e.ends_at', 't.section_id', 't.id as timetable_id',
            ])
            ->get();

        if ($entries->isEmpty()) {
            return collect();
        }

        // خرائط الأسماء (أستاذ/قسم) لعرضها.
        $teacherNames = Teacher::query()
            ->whereIn('id', $entries->pluck('teacher_id')->filter()->unique())
            ->get(['id', 'name'])
            ->mapWithKeys(fn (Teacher $t) => [$t->id => (string) $t->name]);

        $sectionNames = Section::query()
            ->whereIn('id', $entries->pluck('section_id')->unique())
            ->with('classroom:id,name_class')
            ->get(['id', 'classroom_id', 'name_section'])
            ->mapWithKeys(fn (Section $s) => [
                $s->id => trim((optional($s->classroom)->name_class ?? '') . ' / ' . $s->name_section, ' /'),
            ]);

        $conflicts = collect();

        // تعارض الأستاذ: نفس teacher_id في أكثر من قسم لنفس (اليوم، الحصّة).
        $entries->filter(fn ($e) => $e->teacher_id)
            ->groupBy(fn ($e) => $e->teacher_id . '|' . $e->day_of_week . '|' . $e->period_index)
            ->each(function (Collection $group) use (&$conflicts, $teacherNames, $sectionNames) {
                if ($group->pluck('section_id')->unique()->count() < 2) {
                    return;
                }
                $first = $group->first();
                $conflicts->push([
                    'type' => 'teacher',
                    'who' => $teacherNames[$first->teacher_id] ?? ('#' . $first->teacher_id),
                    'day' => (int) $first->day_of_week,
                    'period' => (int) $first->period_index,
                    'times' => $this->timeLabel($first),
                    'items' => $group->map(fn ($e) => [
                        'section' => $sectionNames[$e->section_id] ?? ('#' . $e->section_id),
                        'subject' => $e->subject_name,
                    ])->unique('section')->values()->all(),
                ]);
            });

        // تعارض القاعة: نفس القاعة (غير الفارغة) في أكثر من قسم لنفس (اليوم، الحصّة).
        $entries->filter(fn ($e) => trim((string) $e->room_name) !== '')
            ->groupBy(fn ($e) => mb_strtolower(trim($e->room_name)) . '|' . $e->day_of_week . '|' . $e->period_index)
            ->each(function (Collection $group) use (&$conflicts, $sectionNames) {
                if ($group->pluck('section_id')->unique()->count() < 2) {
                    return;
                }
                $first = $group->first();
                $conflicts->push([
                    'type' => 'room',
                    'who' => $first->room_name,
                    'day' => (int) $first->day_of_week,
                    'period' => (int) $first->period_index,
                    'times' => $this->timeLabel($first),
                    'items' => $group->map(fn ($e) => [
                        'section' => $sectionNames[$e->section_id] ?? ('#' . $e->section_id),
                        'subject' => $e->subject_name,
                    ])->unique('section')->values()->all(),
                ]);
            });

        return $conflicts->sortBy([['day', 'asc'], ['period', 'asc']])->values();
    }

    public function countForSchool(?int $schoolId, ?string $academicYear = null): int
    {
        return $this->forSchool($schoolId, $academicYear)->count();
    }

    private function timeLabel(object $entry): ?string
    {
        $from = $entry->starts_at ? substr((string) $entry->starts_at, 0, 5) : null;
        $to = $entry->ends_at ? substr((string) $entry->ends_at, 0, 5) : null;

        if ($from && $to) {
            return $from . ' - ' . $to;
        }

        return $from ?: null;
    }
}
