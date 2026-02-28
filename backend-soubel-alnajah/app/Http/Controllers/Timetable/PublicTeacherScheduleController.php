<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Inscription\Teacher;
use App\Models\TeacherSchedule\TeacherSchedule;

class PublicTeacherScheduleController extends Controller
{
    private const DAYS = [
        1 => 'السبت',
        2 => 'الأحد',
        3 => 'الإثنين',
        4 => 'الثلاثاء',
        5 => 'الأربعاء',
        6 => 'الخميس',
    ];

    public function index()
    {
        $teacherId = request('teacher_id');
        $academicYear = request('academic_year');

        $teachers = Teacher::query()
            ->select(['id', 'name'])
            ->whereHas('user')
            ->orderByDesc('created_at')
            ->get();

        $years = TeacherSchedule::query()
            ->published()
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        $schedules = TeacherSchedule::query()
            ->published()
            ->with(['teacher:id,user_id,name', 'teacher.user:id,email'])
            ->when(!auth()->check(), fn ($q) => $q->where('visibility', 'public'))
            ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
            ->when($academicYear, fn ($q) => $q->where('academic_year', $academicYear))
            ->orderByDesc('approved_at')
            ->paginate(20)
            ->withQueryString();

        return view('front-end.teacher_schedules.index', [
            'teachers' => $teachers,
            'years' => $years,
            'schedules' => $schedules,
        ]);
    }

    public function show(TeacherSchedule $teacherSchedule)
    {
        $this->ensureCanView($teacherSchedule);

        $teacherSchedule->load(['school', 'teacher.user', 'slots', 'entries', 'teacher.specialization']);

        return view('front-end.teacher_schedules.show', [
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
        ]);
    }

    public function print(TeacherSchedule $teacherSchedule)
    {
        $this->ensureCanView($teacherSchedule);
        $teacherSchedule->load(['school', 'teacher.user', 'slots', 'entries', 'teacher.specialization']);

        return view('front-end.teacher_schedules.print', [
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
        ]);
    }

    public function pdf(TeacherSchedule $teacherSchedule)
    {
        $this->ensureCanView($teacherSchedule);
        $teacherSchedule->load(['teacher.user', 'slots', 'entries', 'teacher.specialization']);

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return redirect()->route('public.teacher_schedules.print', $teacherSchedule);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('front-end.teacher_schedules.print_pdf', [
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('teacher-schedule-' . $teacherSchedule->id . '.pdf');
    }

    private function ensureCanView(TeacherSchedule $teacherSchedule): void
    {
        if ($teacherSchedule->status !== 'published') {
            abort(404);
        }

        if ($teacherSchedule->visibility === 'authenticated' && !auth()->check()) {
            abort(403);
        }
    }

    private function buildMatrix(TeacherSchedule $schedule): array
    {
        $matrix = [];

        foreach ($schedule->entries as $entry) {
            $matrix[$entry->day_of_week][$entry->slot_index] = [
                'subject_name' => $entry->subject_name,
                'class_name' => $entry->class_name,
                'room_name' => $entry->room_name,
                'note' => $entry->note,
            ];
        }

        return $matrix;
    }
}
