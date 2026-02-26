<?php

namespace App\Http\Controllers\Inscription;

use App\Actions\Inscription\CreateTeacherEnrollmentAction;
use App\Actions\Inscription\DeleteTeacherEnrollmentAction;
use App\Actions\Inscription\UpdateTeacherEnrollmentAction;
use App\Models\Inscription\Teacher;
use App\Models\Specialization\Specialization;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacher;
use Throwable;

class TeacherController extends Controller
{
    public function __construct(
        private CreateTeacherEnrollmentAction $createTeacherEnrollmentAction,
        private UpdateTeacherEnrollmentAction $updateTeacherEnrollmentAction,
        private DeleteTeacherEnrollmentAction $deleteTeacherEnrollmentAction
    )
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
        $this->authorize('viewAny', Teacher::class);
        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $specializationId = request('specialization_id');
        $gender = request('gender');

        $data['Teacher'] = Teacher::query()
            ->forSchool($schoolId)
            ->with(['user', 'specialization'])
            ->when($specializationId, fn ($query) => $query->where('specialization_id', $specializationId))
            ->when($gender !== null && $gender !== '', fn ($query) => $query->where('gender', $gender))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($teacherQuery) use ($search) {
                    $teacherQuery->where('name->fr', 'like', '%' . $search . '%')
                        ->orWhere('name->ar', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $data['Specializations'] = Specialization::query()->orderBy('name')->get();
        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('teacher.teacherlist')],
        ];

        return view('admin.teacher', $data);
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
    public function store(StoreTeacher $request)
    {
        $this->authorize('create', Teacher::class);
        $request->validated();

        $schoolId = $this->currentSchoolId();

        try {
            $this->createTeacherEnrollmentAction->execute($request->all(), $schoolId);
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Teachers.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function update(StoreTeacher $request, $id)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $teacher = Teacher::query()->with('user')->findOrFail($id);
        $this->authorize('update', $teacher);

        try {
            $this->updateTeacherEnrollmentAction->execute($teacher, $request->all(), $schoolId);
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success(trans('messages.Update'));

        return redirect()->route('Teachers.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Teacher  $teacher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Teacher $teacher, $id)
    {
        $schoolId = $this->currentSchoolId();

        $teacher = Teacher::query()
            ->with(['user', 'sections'])
            ->findOrFail($id);
        $this->authorize('delete', $teacher);

        if ($teacher->sections()->exists()) {
            toastr()->error('هذا المعلم ينتمي لقسم لا يمكن حذفه');
            return redirect()->route('Teachers.index');
        }

        $this->deleteTeacherEnrollmentAction->execute($teacher);

        toastr()->error(trans('messages.delete'));

        return redirect()->route('Teachers.index');
    }
}
