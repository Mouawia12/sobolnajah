<?php

namespace App\Http\Controllers\School;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use App\Models\School\Section;

use App\Models\Inscription\Teacher;

use App\Http\Requests\StoreSection;
use App\Http\Requests\SyncSectionTeachersRequest;
use App\Http\Requests\UpdateSectionStatusRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class SectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['getSection', 'getSection2', 'listByClassroom', 'getSectionById']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Section::class);
        $schoolId = $this->currentSchoolId();
        $query = trim((string) request('q'));
        $gradeFilter = request('grade_id');
        $classroomFilter = request('classroom_id');
        $statusFilter = request('status');

        if ($schoolId) {
            $gradeFilter = $gradeFilter ?: null;
            $classroomFilter = $classroomFilter ?: null;
        }

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->with('schoolgrades')
            ->orderBy('name_school')
            ->get();

        $data['SchoolgradeFilterOptions'] = Schoolgrade::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->orderBy('name_grade')
            ->get();

        $data['ClassroomFilterOptions'] = Classroom::query()
            ->forSchool($schoolId)
            ->with('schoolgrade')
            ->orderBy('name_class')
            ->get();

        $sectionFilters = function ($sectionsQuery) use ($schoolId, $gradeFilter, $classroomFilter, $statusFilter, $query) {
            $sectionsQuery
                ->forSchool($schoolId)
                ->with(['classroom.schoolgrade.school', 'teachers'])
                ->when($gradeFilter, fn ($q) => $q->where('grade_id', (int) $gradeFilter))
                ->when($classroomFilter, fn ($q) => $q->where('classroom_id', (int) $classroomFilter))
                ->when($statusFilter !== null && $statusFilter !== '', fn ($q) => $q->where('Status', (int) $statusFilter))
                ->when($query !== '', function ($q) use ($query) {
                    $q->where(function ($textQuery) use ($query) {
                        $textQuery->where('name_section->fr', 'like', '%' . $query . '%')
                            ->orWhere('name_section->ar', 'like', '%' . $query . '%')
                            ->orWhere('name_section->en', 'like', '%' . $query . '%')
                            ->orWhereHas('classroom', function ($classroomQuery) use ($query) {
                                $classroomQuery->where('name_class->fr', 'like', '%' . $query . '%')
                                    ->orWhere('name_class->ar', 'like', '%' . $query . '%')
                                    ->orWhere('name_class->en', 'like', '%' . $query . '%');
                            });
                    });
                })
                ->orderByDesc('created_at');
        };

        $data['Schoolgrade'] = Schoolgrade::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($gradeFilter, fn ($q) => $q->whereKey((int) $gradeFilter))
            ->whereHas('sections', $sectionFilters)
            ->with([
                'school',
                'sections' => $sectionFilters,
            ])
            ->orderBy('name_grade')
            ->paginate(6)
            ->withQueryString();

        $data['Teacher'] = Teacher::query()
            ->forSchool($schoolId)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $data['notify'] = $this->notifications();
        $data['currentSchoolId'] = $schoolId;

        return view('admin.sections', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSection $request)
    {
        $this->authorize('create', Section::class);
        $request->validated();

        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $request->school_id !== $schoolId) {
            return back()->withErrors(['school_id' => trans('messages.error')]);
        }

        $teacherIds = collect($request->teacher_id)->filter()->unique()->all();

        if ($schoolId) {
            $teacherIds = Teacher::query()
                ->forSchool($schoolId)
                ->whereIn('id', $teacherIds)
                ->pluck('id')
                ->all();
        }

        try {
            DB::transaction(function () use ($request, $teacherIds) {
                $section = Section::create([
                    'school_id' => $request->school_id,
                    'grade_id' => $request->grade_id,
                    'classroom_id' => $request->classroom_id,
                    'name_section' => [
                        'fr' => $request->name_sectionfr,
                        'en' => $request->name_sectionfr,
                        'ar' => $request->name_sectionar,
                    ],
                    'Status' => 1,
                ]);

                if (!empty($teacherIds)) {
                    $section->teachers()->sync($teacherIds);
                }

                $this->forgetClassSectionsLookupCache((int) $section->school_id, (int) $section->classroom_id);
                $this->forgetSectionByIdLookupCache((int) $section->school_id, (int) $section->id);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Sections.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        abort(405);
    }

    public function updateStatus(UpdateSectionStatusRequest $request, $id)
    {
        $validated = $request->validated();

        $section = Section::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($id);
        $this->authorize('update', $section);

        $section->update([
            'Status' => (int) $validated['statu'],
        ]);
        $this->forgetSectionByIdLookupCache((int) $section->school_id, (int) $section->id);

        toastr()->success(trans('messages.Update'));
        return redirect()->route('Sections.index');
    }

    public function syncTeachers(SyncSectionTeachersRequest $request, $id)
    {
        $validated = $request->validated();

        $section = Section::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($id);
        $this->authorize('update', $section);

            $teacherIds = Teacher::query()
                ->forSchool($this->currentSchoolId())
                ->whereIn('id', (array) ($validated['teacher_id'] ?? []))
                ->pluck('id')
                ->all();

        $section->teachers()->sync($teacherIds);
        $section->save();
        $this->forgetSectionByIdLookupCache((int) $section->school_id, (int) $section->id);

        toastr()->success(trans('messages.Update'));
        return redirect()->route('Sections.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreSection $request, $id)
    {
        try {
            $validated = $request->validated();

            $Sections = Section::query()
                ->forSchool($this->currentSchoolId())
                ->findOrFail($id);
            $this->authorize('update', $Sections);
            $originalClassroomId = (int) $Sections->classroom_id;

            if ($this->currentSchoolId() && (int) $request->school_id !== $Sections->school_id) {
                return back()->withErrors(['school_id' => trans('messages.error')]);
            }

            $teacherIds = Teacher::query()
                ->forSchool($this->currentSchoolId())
                ->whereIn('id', (array) ($request->teacher_id ?? []))
                ->pluck('id')
                ->all();

            // إضافة ترجمة الانجليزية بنفس قيمة الفرنسية
            $Sections->name_section = [
                'fr' => $request->name_sectionfr,
                'en' => $request->name_sectionfr,
                'ar' => $request->name_sectionar
            ];
            $Sections->grade_id = $request->grade_id;
            $Sections->classroom_id = $request->classroom_id;
            $Sections->Status = $request->statu;

            //update pivot tABLE
            $Sections->teachers()->sync($teacherIds);

            $Sections->save();
            $this->forgetClassSectionsLookupCache((int) $Sections->school_id, $originalClassroomId);
            $this->forgetClassSectionsLookupCache((int) $Sections->school_id, (int) $Sections->classroom_id);
            $this->forgetSectionByIdLookupCache((int) $Sections->school_id, (int) $Sections->id);

            toastr()->success(trans('messages.Update'));
            return redirect()->route('Sections.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $section = Section::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($id);
        $this->authorize('delete', $section);

        $schoolId = (int) $section->school_id;
        $classroomId = (int) $section->classroom_id;
        $sectionId = (int) $section->id;
        $section->delete();
        $this->forgetClassSectionsLookupCache($schoolId, $classroomId);
        $this->forgetSectionByIdLookupCache($schoolId, $sectionId);
        toastr()->error(trans('messages.delete'));
        return redirect()->route('Sections.index');
    }

    public function listByClassroom($id)
    {
        $schoolId = (int) ($this->currentSchoolId() ?? 0);
        $cacheKey = sprintf('lookup:school:%d:classroom:%d:sections', $schoolId, (int) $id);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($id) {
            return Section::query()
                ->forSchool($this->currentSchoolId())
                ->where("classroom_id", $id)
                ->pluck("name_section", "id");
        });
    }

    public function getSectionById($id)
    {
        $schoolId = (int) ($this->currentSchoolId() ?? 0);
        $cacheKey = sprintf('lookup:school:%d:section:%d', $schoolId, (int) $id);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($id) {
            return Section::query()
                ->forSchool($this->currentSchoolId())
                ->where("id", $id)
                ->pluck("name_section", "id");
        });
    }

    // Backward-compatible aliases for legacy naming.
    public function getSection($id)
    {
        return $this->listByClassroom($id);
    }

    public function getSection2($id)
    {
        return $this->getSectionById($id);
    }

    private function forgetClassSectionsLookupCache(int $schoolId, int $classroomId): void
    {
        Cache::forget(sprintf('lookup:school:%d:classroom:%d:sections', $schoolId, $classroomId));
    }

    private function forgetSectionByIdLookupCache(int $schoolId, int $sectionId): void
    {
        Cache::forget(sprintf('lookup:school:%d:section:%d', $schoolId, $sectionId));
    }
}
