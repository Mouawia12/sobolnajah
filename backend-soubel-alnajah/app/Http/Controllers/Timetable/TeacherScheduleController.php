<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyTeacherScheduleRequest;
use App\Http\Requests\StoreTeacherScheduleRequest;
use App\Http\Requests\UpdateTeacherScheduleRequest;
use App\Models\Inscription\Teacher;
use App\Models\TeacherSchedule\TeacherSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class TeacherScheduleController extends Controller
{
    private const DEFAULT_SLOTS = [
        ['slot_index' => 1, 'label' => '08:00-09:00', 'starts_at' => '08:00', 'ends_at' => '09:00'],
        ['slot_index' => 2, 'label' => '09:00-10:00', 'starts_at' => '09:00', 'ends_at' => '10:00'],
        ['slot_index' => 3, 'label' => '10:00-11:00', 'starts_at' => '10:00', 'ends_at' => '11:00'],
        ['slot_index' => 4, 'label' => '11:00-12:00', 'starts_at' => '11:00', 'ends_at' => '12:00'],
        ['slot_index' => 5, 'label' => '12:00-13:00', 'starts_at' => '12:00', 'ends_at' => '13:00'],
    ];

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
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index()
    {
        $this->authorize('viewAny', TeacherSchedule::class);

        $schoolId = $this->currentSchoolId();
        $q = trim((string) request('q'));
        $teacherId = request('teacher_id');
        $academicYear = request('academic_year');
        $status = request('status');

        $schedules = TeacherSchedule::query()
            ->forSchool($schoolId)
            ->withCount('entries')
            ->with(['teacher:id,user_id,name', 'teacher.user:id,email'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('title', 'like', '%' . $q . '%')
                        ->orWhere('branch_name', 'like', '%' . $q . '%')
                        ->orWhereHas('teacher.user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $q . '%'));
                });
            })
            ->when($teacherId, fn ($query) => $query->where('teacher_id', $teacherId))
            ->when($academicYear, fn ($query) => $query->where('academic_year', $academicYear))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $teachers = Teacher::query()
            ->forSchool($schoolId)
            ->select(['id', 'user_id', 'name'])
            ->with('user:id,email')
            ->orderByDesc('created_at')
            ->get();

        $years = TeacherSchedule::query()
            ->forSchool($schoolId)
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('admin.teacher_schedules.index', [
            'notify' => $this->notifications(),
            'schedules' => $schedules,
            'teachers' => $teachers,
            'years' => $years,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'التوقيت الأسبوعي للأساتذة'],
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', TeacherSchedule::class);

        $teachers = Teacher::query()
            ->forSchool($this->currentSchoolId())
            ->select(['id', 'name'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.teacher_schedules.create', [
            'notify' => $this->notifications(),
            'teachers' => $teachers,
            'defaultSlots' => self::DEFAULT_SLOTS,
            'days' => self::DAYS,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'التوقيت الأسبوعي للأساتذة', 'url' => route('teacher-schedules.index')],
                ['label' => 'إضافة جدول أستاذ'],
            ],
        ]);
    }

    public function store(StoreTeacherScheduleRequest $request)
    {
        $this->authorize('create', TeacherSchedule::class);
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId();
        $teacher = Teacher::query()->forSchool($schoolId)->findOrFail((int) $validated['teacher_id']);

        try {
            DB::transaction(function () use ($validated, $teacher) {
                $schedule = TeacherSchedule::query()->create([
                    'school_id' => $teacher->user?->school_id ?: $this->currentSchoolId(),
                    'teacher_id' => $teacher->id,
                    'academic_year' => $validated['academic_year'],
                    'title' => $validated['title'] ?? null,
                    'branch_name' => $validated['branch_name'] ?? null,
                    'status' => $validated['status'],
                    'visibility' => $validated['visibility'],
                    'approved_at' => $validated['approved_at'] ?? null,
                    'signature_text' => $validated['signature_text'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                $this->syncSlotsAndEntries($schedule, $validated);
            });
        } catch (Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        toastr()->success('تم إنشاء جدول الأستاذ بنجاح');
        return redirect()->route('teacher-schedules.index');
    }

    public function show(TeacherSchedule $teacherSchedule)
    {
        $this->authorize('view', $teacherSchedule);
        $teacherSchedule->load(['school', 'teacher.user', 'teacher.specialization', 'slots', 'entries']);

        return view('admin.teacher_schedules.show', [
            'notify' => $this->notifications(),
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'التوقيت الأسبوعي للأساتذة', 'url' => route('teacher-schedules.index')],
                ['label' => 'عرض الجدول'],
            ],
        ]);
    }

    public function edit(TeacherSchedule $teacherSchedule)
    {
        $this->authorize('update', $teacherSchedule);

        $teachers = Teacher::query()
            ->forSchool($this->currentSchoolId())
            ->select(['id', 'name'])
            ->orderByDesc('created_at')
            ->get();

        $teacherSchedule->load(['school', 'slots', 'entries']);

        return view('admin.teacher_schedules.edit', [
            'notify' => $this->notifications(),
            'schedule' => $teacherSchedule,
            'teachers' => $teachers,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'التوقيت الأسبوعي للأساتذة', 'url' => route('teacher-schedules.index')],
                ['label' => 'تعديل الجدول'],
            ],
        ]);
    }

    public function update(UpdateTeacherScheduleRequest $request, TeacherSchedule $teacherSchedule)
    {
        $this->authorize('update', $teacherSchedule);
        $validated = $request->validated();

        $teacher = Teacher::query()->forSchool($this->currentSchoolId())->findOrFail((int) $validated['teacher_id']);

        try {
            DB::transaction(function () use ($validated, $teacherSchedule, $teacher) {
                $teacherSchedule->update([
                    'school_id' => $teacher->user?->school_id ?: $this->currentSchoolId(),
                    'teacher_id' => $teacher->id,
                    'academic_year' => $validated['academic_year'],
                    'title' => $validated['title'] ?? null,
                    'branch_name' => $validated['branch_name'] ?? null,
                    'status' => $validated['status'],
                    'visibility' => $validated['visibility'],
                    'approved_at' => $validated['approved_at'] ?? null,
                    'signature_text' => $validated['signature_text'] ?? null,
                    'updated_by' => auth()->id(),
                ]);

                $this->syncSlotsAndEntries($teacherSchedule, $validated);
            });
        } catch (Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        toastr()->success('تم تحديث جدول الأستاذ بنجاح');
        return redirect()->route('teacher-schedules.index');
    }

    public function destroy(DestroyTeacherScheduleRequest $request, TeacherSchedule $teacherSchedule)
    {
        $this->authorize('delete', $teacherSchedule);
        $teacherSchedule->delete();
        toastr()->success('تم حذف جدول الأستاذ');

        return redirect()->route('teacher-schedules.index');
    }

    public function print(TeacherSchedule $teacherSchedule)
    {
        $this->authorize('view', $teacherSchedule);
        $teacherSchedule->load(['school', 'teacher.user', 'slots', 'entries', 'teacher.specialization']);

        return view('admin.teacher_schedules.print', [
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
        ]);
    }

    public function pdf(TeacherSchedule $teacherSchedule)
    {
        $this->authorize('view', $teacherSchedule);
        $teacherSchedule->load(['school', 'teacher.user', 'slots', 'entries', 'teacher.specialization']);

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return redirect()->route('teacher-schedules.print', $teacherSchedule);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.teacher_schedules.print_pdf', [
            'schedule' => $teacherSchedule,
            'days' => self::DAYS,
            'matrix' => $this->buildMatrix($teacherSchedule),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('teacher-schedule-' . $teacherSchedule->id . '.pdf');
    }

    private function syncSlotsAndEntries(TeacherSchedule $schedule, array $validated): void
    {
        $slots = collect($validated['slots'])
            ->map(fn ($slot) => [
                'slot_index' => (int) $slot['slot_index'],
                'label' => $slot['label'] ?? null,
                'starts_at' => $slot['starts_at'] ?? null,
                'ends_at' => $slot['ends_at'] ?? null,
            ])
            ->sortBy('slot_index')
            ->values();

        $schedule->slots()->delete();
        foreach ($slots as $slot) {
            $schedule->slots()->create($slot);
        }

        $schedule->entries()->delete();

        $entries = $validated['entries'] ?? [];
        foreach (self::DAYS as $day => $_label) {
            foreach ($slots as $slot) {
                $slotIndex = (int) $slot['slot_index'];
                $cell = $entries[$day][$slotIndex] ?? [];

                $payload = [
                    'subject_name' => trim((string) ($cell['subject_name'] ?? '')),
                    'class_name' => trim((string) ($cell['class_name'] ?? '')),
                    'room_name' => trim((string) ($cell['room_name'] ?? '')),
                    'note' => trim((string) ($cell['note'] ?? '')),
                ];

                if (collect($payload)->every(fn ($value) => $value === '')) {
                    continue;
                }

                $schedule->entries()->create([
                    'day_of_week' => (int) $day,
                    'slot_index' => $slotIndex,
                    'subject_name' => $payload['subject_name'] ?: null,
                    'class_name' => $payload['class_name'] ?: null,
                    'room_name' => $payload['room_name'] ?: null,
                    'note' => $payload['note'] ?: null,
                ]);
            }
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
