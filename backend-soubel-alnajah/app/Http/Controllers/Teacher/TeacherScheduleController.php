<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherSchedule\TeacherSchedule;
use Illuminate\Support\Facades\Auth;

class TeacherScheduleController extends Controller
{
    private const DAYS = [
        1 => 'السبت',
        2 => 'الأحد',
        3 => 'الإثنين',
        4 => 'الثلاثاء',
        5 => 'الأربعاء',
        6 => 'الخميس',
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'role:teacher', 'force.password.change']);
    }

    public function index()
    {
        $teacher = Auth::user()?->loadMissing('roles');
        $academicYear = request('academic_year');

        $schedules = TeacherSchedule::query()
            ->whereHas('teacher', fn ($q) => $q->where('user_id', Auth::id()))
            ->when($academicYear, fn ($q) => $q->where('academic_year', $academicYear))
            ->orderByDesc('academic_year')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $years = TeacherSchedule::query()
            ->whereHas('teacher', fn ($q) => $q->where('user_id', Auth::id()))
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('teacher.schedules.index', [
            'notify' => $this->notifications(),
            'schedules' => $schedules,
            'years' => $years,
            'breadcrumbs' => [
                ['label' => 'لوحة المعلم', 'url' => route('teacher.dashboard')],
                ['label' => 'جدولي الأسبوعي'],
            ],
        ]);
    }

    public function show(TeacherSchedule $teacherSchedule)
    {
        $this->abortIfNotOwner($teacherSchedule);
        $teacherSchedule->load(['teacher.user', 'slots', 'entries']);

        return view('teacher.schedules.show', [
            'notify' => $this->notifications(),
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
            'breadcrumbs' => [
                ['label' => 'لوحة المعلم', 'url' => route('teacher.dashboard')],
                ['label' => 'جدولي الأسبوعي', 'url' => route('teacher.schedules.index')],
                ['label' => 'عرض الجدول'],
            ],
        ]);
    }

    public function print(TeacherSchedule $teacherSchedule)
    {
        $this->abortIfNotOwner($teacherSchedule);
        $teacherSchedule->load(['school', 'teacher.user', 'slots', 'entries', 'teacher.specialization']);

        return view('teacher.schedules.print', [
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
        ]);
    }

    public function pdf(TeacherSchedule $teacherSchedule)
    {
        $this->abortIfNotOwner($teacherSchedule);
        $teacherSchedule->load(['school', 'teacher.user', 'slots', 'entries', 'teacher.specialization']);

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return redirect()->route('teacher.schedules.print', $teacherSchedule);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('teacher.schedules.print_pdf', [
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('my-schedule-' . $teacherSchedule->id . '.pdf');
    }

    private function abortIfNotOwner(TeacherSchedule $teacherSchedule): void
    {
        $ownerId = (int) ($teacherSchedule->teacher?->user_id ?? 0);
        abort_if($ownerId !== (int) Auth::id(), 403);
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
