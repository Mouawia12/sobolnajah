<?php

namespace App\Http\Controllers\School;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteBulkInscriptionsRequest;
use App\Http\Requests\DestroyClassroomRequest;
use Illuminate\Http\Request;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Classroom;
use App\Models\Inscription\Inscription;
use App\Http\Requests\StoreClassroom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Throwable;







class ClassroomController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin'])->except(['getClasse', 'listByGrade']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', Classroom::class);
        $schoolId = $this->currentSchoolId();
        $query = trim((string) request('q'));
        $schoolFilter = request('school_id');
        $gradeFilter = request('grade_id');

        if ($schoolId) {
            $schoolFilter = $schoolId;
        }

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->select(['id', 'name_school'])
            ->orderBy('name_school')
            ->get();

        $data['Schoolgradee'] = Schoolgrade::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->select(['id', 'name_grade'])
            ->orderBy('name_grade')
            ->get();

        $data['Classroom'] = Classroom::query()
            ->forSchool($schoolId)
            ->select(['id', 'school_id', 'grade_id', 'name_class', 'created_at'])
            ->with([
                'schoolgrade:id,school_id,name_grade',
                'schoolgrade.school:id,name_school',
            ])
            ->when($schoolFilter, fn ($classroomQuery) => $classroomQuery->where('school_id', (int) $schoolFilter))
            ->when($gradeFilter, fn ($classroomQuery) => $classroomQuery->where('grade_id', (int) $gradeFilter))
            ->when($query !== '', function ($classroomQuery) use ($query) {
                $classroomQuery->where(function ($textQuery) use ($query) {
                    $textQuery->where('name_class->fr', 'like', '%' . $query . '%')
                        ->orWhere('name_class->ar', 'like', '%' . $query . '%')
                        ->orWhere('name_class->en', 'like', '%' . $query . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();
        $data['currentSchoolId'] = $schoolId;

        $data['notify'] = $this->notifications();

        return view('admin.classes', $data);
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
    public function store(StoreClassroom $request)
    {
        $this->authorize('create', Classroom::class);
        $request->validated();

        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $request->school_id !== $schoolId) {
            return back()->withErrors(['school_id' => trans('messages.error')]);
        }

        try {
            DB::transaction(function () use ($request) {
                $classroom = Classroom::create([
                    'school_id' => $request->school_id,
                    'grade_id' => $request->grade_id,
                    'name_class' => [
                        'fr' => $request->name_classfr,
                        'ar' => $request->name_classar,
                        'en' => $request->name_classfr,
                    ],
                ]);
                $this->forgetGradeClassesLookupCache((int) $classroom->school_id, (int) $classroom->grade_id);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Classes.index');
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
    public function edit($id)
    {
        //
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreClassroom $request, $id)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $classroom = Classroom::query()
            ->forSchool($schoolId)
            ->findOrFail($id);
        $this->authorize('update', $classroom);

        if ($schoolId && (int) $request->school_id !== $classroom->school_id) {
            return back()->withErrors(['school_id' => trans('messages.error')]);
        }

        try {
            $originalGradeId = (int) $classroom->grade_id;
            DB::transaction(function () use ($classroom, $request, $originalGradeId) {
                $classroom->update([
                    'name_class' => [
                        'fr' => $request->name_classfr,
                        'ar' => $request->name_classar,
                        'en' => $request->name_classfr,
                    ],
                    'grade_id' => $request->grade_id,
                ]);
                $this->forgetGradeClassesLookupCache((int) $classroom->school_id, $originalGradeId);
                $this->forgetGradeClassesLookupCache((int) $classroom->school_id, (int) $request->grade_id);
            });
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.Update'));

        return redirect()->route('Classes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyClassroomRequest $request, $id)
    {
        $validated = $request->validated();
        $classroom = Classroom::query()
            ->forSchool($this->currentSchoolId())
            ->findOrFail((int) $validated['id']);
        $this->authorize('delete', $classroom);

        $hasInscriptions = Inscription::where('classroom_id', $classroom->id)->exists();

        if ($hasInscriptions) {
            toastr()->error(trans('messages.cantdelete'));
            return redirect()->route('Classes.index');
        }

        $classroom->delete();
        $this->forgetGradeClassesLookupCache((int) $classroom->school_id, (int) $classroom->grade_id);

        toastr()->error(trans('messages.delete'));

        return redirect()->route('Classes.index');
    }


    
    public function listByGrade($id)
    {
        $schoolId = (int) ($this->currentSchoolId() ?? 0);
        $cacheKey = sprintf('lookup:school:%d:grade:%d:classes', $schoolId, (int) $id);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($id) {
            return Classroom::query()
                ->forSchool($this->currentSchoolId())
                ->where("grade_id", $id)
                ->pluck("name_class", "id");
        });
    }

    private function forgetGradeClassesLookupCache(int $schoolId, int $gradeId): void
    {
        Cache::forget(sprintf('lookup:school:%d:grade:%d:classes', $schoolId, $gradeId));
        Cache::forget(sprintf('exam:school:%d:classrooms', $schoolId));
    }

    // Backward-compatible alias for legacy naming.
    public function getClasse($id){
        return $this->listByGrade($id);
    }


    public function delete_all(DeleteBulkInscriptionsRequest $request)
    {
        $validated = $request->validated();

        $delete_all_id = collect(explode(",", $validated['delete_all_id']))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($delete_all_id)) {
            return redirect()->route('Inscriptions.index')
                ->withErrors(['delete_all_id' => trans('messages.error')]);
        }

        Inscription::query()
            ->where('school_id', $this->currentSchoolId())
            ->whereIn('id', $delete_all_id)
            ->delete();

        toastr()->error(trans('messages.delete'));
        return redirect()->route('Inscriptions.index');
    }
    
   

}
