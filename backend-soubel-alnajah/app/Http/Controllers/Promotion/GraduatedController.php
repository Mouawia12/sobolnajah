<?php

namespace App\Http\Controllers\Promotion;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyGraduatedRequest;
use App\Http\Requests\RestoreGraduatedStudentRequest;
use App\Http\Requests\StoreGraduatedRequest;

use App\Models\School\School;
use App\Models\School\Section;
use App\Models\Inscription\StudentInfo;
use App\Models\Promotion\Promotion;
use App\Models\User;

use Throwable;

class GraduatedController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', StudentInfo::class);
        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $sectionId = request('section_id');

        $data['School'] = School::query()
            ->forSchool($schoolId)
            ->get();
        $data['Sections'] = Section::query()
            ->forSchool($schoolId)
            ->with('classroom.schoolgrade')
            ->orderBy('id')
            ->get();
        $data['StudentInfo'] = StudentInfo::query()
            ->onlyTrashed()
            ->forSchool($schoolId)
            ->with(['user', 'section.classroom.schoolgrade.school'])
            ->when($sectionId, fn ($query) => $query->where('section_id', (int) $sectionId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery->where('prenom->fr', 'like', '%' . $search . '%')
                        ->orWhere('prenom->ar', 'like', '%' . $search . '%')
                        ->orWhere('nom->fr', 'like', '%' . $search . '%')
                        ->orWhere('nom->ar', 'like', '%' . $search . '%')
                        ->orWhere('numtelephone', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
                });
            })
            ->orderByDesc('deleted_at')
            ->paginate(20)
            ->withQueryString();
        $data['notify'] = $this->notifications();


        return view('admin.graduated',$data);
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
    public function store(StoreGraduatedRequest $request)
    {
        $this->authorize('create', StudentInfo::class);
        $validated = $request->validated();

        try {
            $schoolId = $this->currentSchoolId();

            $students = StudentInfo::query()
                ->forSchool($schoolId)
                ->where('section_id', $validated['section_id'])
                ->get();

            if ($students->isEmpty()) {
                return redirect()->back()->withErrors(['error' => trans('messages.error')]);
            }

            foreach ($students as $student) {
                $this->authorize('delete', $student);
                $student->delete();
                Promotion::query()
                    ->where('student_id', $student->id)
                    ->delete();
            } 

            toastr()->success(trans('messages.success'));
            return redirect()->route('graduated.index');

        } catch (Throwable $e) {
            
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
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
    public function update(RestoreGraduatedStudentRequest $request, $id)
    {
        $request->validated();

        $student = StudentInfo::query()
            ->onlyTrashed()
            ->forSchool($this->currentSchoolId())
            ->findOrFail($id);
        $this->authorize('update', $student);

        $student->restore();
        toastr()->success(trans('messages.success'));
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestroyGraduatedRequest $request)
    {
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId();

        if ((int) $validated['delete_id'] === 1) {
            $student = StudentInfo::query()
                ->onlyTrashed()
                ->forSchool($schoolId)
                ->with(['user', 'parent.user'])
                ->findOrFail($validated['student_id']);
            $this->authorize('delete', $student);

            $userIds = collect([
                $student->user?->id,
                $student->parent?->user?->id,
            ])->filter()->all();

            if (!empty($userIds)) {
                User::query()->whereIn('id', $userIds)->delete();
            }

            $student->forceDelete();

            toastr()->error(trans('messages.delete'));
            return redirect()->route('graduated.index');
        }

        $student = StudentInfo::query()
            ->forSchool($schoolId)
            ->findOrFail($validated['student_id']);
        $this->authorize('delete', $student);

        $student->delete();

        if (!empty($validated['promotion_id'])) {
            Promotion::query()
                ->when($schoolId, fn ($query) => $query->where('from_school', $schoolId))
                ->where('id', $validated['promotion_id'])
                ->delete();
        }

        toastr()->error(trans('messages.delete'));
        return redirect()->route('Promotions.index');
    }
}
