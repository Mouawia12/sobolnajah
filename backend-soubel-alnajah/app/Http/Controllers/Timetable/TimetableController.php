<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyTimetableRequest;
use App\Http\Requests\StoreTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Models\Inscription\Teacher;
use App\Models\School\Section;
use App\Models\Timetable\Timetable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Throwable;

class TimetableController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index()
    {
        $this->authorize('viewAny', Timetable::class);

        $schoolId = $this->currentSchoolId();
        $query = request('q');
        $sectionId = request('section_id');

        $timetables = Timetable::query()
            ->forSchool($schoolId)
            ->select(['id', 'section_id', 'academic_year', 'title', 'is_published', 'created_at'])
            ->withCount('entries')
            ->with([
                'section:id,classroom_id,name_section',
                'section.classroom:id,grade_id,name_class',
                'section.classroom.schoolgrade:id,name_grade',
            ])
            ->when($query, fn ($builder) => $builder->where('title', 'like', '%' . $query . '%'))
            ->when($sectionId, fn ($builder) => $builder->where('section_id', $sectionId))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $sections = Section::query()
            ->forSchool($schoolId)
            ->select(['id', 'classroom_id', 'name_section'])
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->orderBy('created_at')
            ->get();

        return view('admin.timetables.index', [
            'notify' => $this->notifications(),
            'timetables' => $timetables,
            'sections' => $sections,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'الجداول'],
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Timetable::class);

        $schoolId = $this->currentSchoolId();
        $sections = Section::query()
            ->forSchool($schoolId)
            ->select(['id', 'classroom_id', 'name_section', 'created_at'])
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->orderBy('created_at')
            ->get();
        $teachers = Teacher::query()
            ->forSchool($schoolId)
            ->select(['id', 'name', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.timetables.create', [
            'notify' => $this->notifications(),
            'sections' => $sections,
            'teachers' => $teachers,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'الجداول', 'url' => route('timetables.index')],
                ['label' => 'إضافة جدول'],
            ],
        ]);
    }

    public function store(StoreTimetableRequest $request)
    {
        $this->authorize('create', Timetable::class);
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId();
        $section = Section::query()->forSchool($schoolId)->findOrFail($validated['section_id']);

        try {
            DB::transaction(function () use ($validated, $section) {
                $timetable = Timetable::query()->create([
                    'school_id' => $section->school_id,
                    'section_id' => $section->id,
                    'academic_year' => $validated['academic_year'],
                    'title' => $validated['title'] ?? null,
                    'starts_on' => $validated['starts_on'] ?? null,
                    'ends_on' => $validated['ends_on'] ?? null,
                    'is_published' => (bool) ($validated['is_published'] ?? false),
                    'published_at' => !empty($validated['is_published']) ? now() : null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                foreach ($validated['entries'] as $entry) {
                    $timetable->entries()->create([
                        'day_of_week' => (int) $entry['day_of_week'],
                        'period_index' => (int) $entry['period_index'],
                        'starts_at' => $entry['starts_at'] ?? null,
                        'ends_at' => $entry['ends_at'] ?? null,
                        'subject_name' => $entry['subject_name'],
                        'teacher_id' => $entry['teacher_id'] ?? null,
                        'room_name' => $entry['room_name'] ?? null,
                        'notes' => $entry['notes'] ?? null,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success('تم إنشاء الجدول بنجاح');
        $this->forgetPublicSectionsCache();
        return redirect()->route('timetables.index');
    }

    public function edit(Timetable $timetable)
    {
        $this->authorize('update', $timetable);

        $schoolId = $this->currentSchoolId();
        $sections = Section::query()
            ->forSchool($schoolId)
            ->select(['id', 'classroom_id', 'name_section', 'created_at'])
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->orderBy('created_at')
            ->get();
        $teachers = Teacher::query()
            ->forSchool($schoolId)
            ->select(['id', 'name', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        $timetable->load([
            'entries:id,timetable_id,day_of_week,period_index,starts_at,ends_at,subject_name,teacher_id,room_name',
        ]);

        return view('admin.timetables.edit', [
            'notify' => $this->notifications(),
            'timetable' => $timetable,
            'sections' => $sections,
            'teachers' => $teachers,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'الجداول', 'url' => route('timetables.index')],
                ['label' => 'تعديل جدول'],
            ],
        ]);
    }

    public function update(UpdateTimetableRequest $request, Timetable $timetable)
    {
        $this->authorize('update', $timetable);
        $validated = $request->validated();

        $section = Section::query()->forSchool($this->currentSchoolId())->findOrFail($validated['section_id']);

        try {
            DB::transaction(function () use ($validated, $timetable, $section) {
                $timetable->update([
                    'school_id' => $section->school_id,
                    'section_id' => $section->id,
                    'academic_year' => $validated['academic_year'],
                    'title' => $validated['title'] ?? null,
                    'starts_on' => $validated['starts_on'] ?? null,
                    'ends_on' => $validated['ends_on'] ?? null,
                    'is_published' => (bool) ($validated['is_published'] ?? false),
                    'published_at' => !empty($validated['is_published']) ? ($timetable->published_at ?? now()) : null,
                    'updated_by' => auth()->id(),
                ]);

                $timetable->entries()->delete();
                foreach ($validated['entries'] as $entry) {
                    $timetable->entries()->create([
                        'day_of_week' => (int) $entry['day_of_week'],
                        'period_index' => (int) $entry['period_index'],
                        'starts_at' => $entry['starts_at'] ?? null,
                        'ends_at' => $entry['ends_at'] ?? null,
                        'subject_name' => $entry['subject_name'],
                        'teacher_id' => $entry['teacher_id'] ?? null,
                        'room_name' => $entry['room_name'] ?? null,
                        'notes' => $entry['notes'] ?? null,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success('تم تحديث الجدول');
        $this->forgetPublicSectionsCache();
        return redirect()->route('timetables.index');
    }

    public function destroy(DestroyTimetableRequest $request)
    {
        $validated = $request->validated();
        $timetable = Timetable::query()->findOrFail((int) $validated['id']);
        $this->authorize('delete', $timetable);
        $timetable->delete();
        $this->forgetPublicSectionsCache();
        toastr()->error('تم حذف الجدول');
        return redirect()->route('timetables.index');
    }

    public function print(Timetable $timetable)
    {
        $this->authorize('view', $timetable);
        $timetable->load(['entries.teacher', 'section.classroom.schoolgrade.school']);

        return view('admin.timetables.print', [
            'timetable' => $timetable,
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'الجداول', 'url' => route('timetables.index')],
                ['label' => 'طباعة جدول'],
            ],
        ]);
    }

    private function forgetPublicSectionsCache(): void
    {
        Cache::forget(PublicTimetableController::SECTIONS_CACHE_KEY);
    }
}
