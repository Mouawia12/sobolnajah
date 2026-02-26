<?php

namespace App\Http\Controllers\Inscription;

use App\Actions\Inscription\BuildStudentEnrollmentPayloadAction;
use App\Actions\Inscription\DeleteStudentEnrollmentAction;
use App\Actions\Inscription\UpdateStudentEnrollmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\StoreStudent;
use App\Imports\StudentsImport;
use App\Models\Inscription\StudentInfo;
use App\Models\School\School;
use App\Models\School\Section;
use App\Services\StudentEnrollmentService;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class StudentController extends Controller
{
    public function __construct(
        private StudentEnrollmentService $enrollmentService,
        private BuildStudentEnrollmentPayloadAction $buildStudentEnrollmentPayloadAction,
        private UpdateStudentEnrollmentAction $updateStudentEnrollmentAction,
        private DeleteStudentEnrollmentAction $deleteStudentEnrollmentAction
    )
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $this->authorize('viewAny', StudentInfo::class);
        $schoolId = $this->currentSchoolId();
        $search = trim((string) request('q'));
        $sectionId = request('section_id');
        $classroomId = request('classroom_id');
        $gradeId = request('grade_id');

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->with('schoolgrades')
            ->orderBy('name_school')
            ->get();

        $data['StudentInfo'] = StudentInfo::query()
            ->forSchool($schoolId)
            ->with(['user', 'parent.user', 'section.classroom.school'])
            ->when($sectionId, fn ($query) => $query->where('section_id', $sectionId))
            ->when($classroomId, function ($query) use ($classroomId) {
                $query->whereHas('section', fn ($sectionQuery) => $sectionQuery->where('classroom_id', $classroomId));
            })
            ->when($gradeId, function ($query) use ($gradeId) {
                $query->whereHas('section.classroom', fn ($classroomQuery) => $classroomQuery->where('grade_id', $gradeId));
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery->where('prenom->fr', 'like', '%' . $search . '%')
                        ->orWhere('prenom->ar', 'like', '%' . $search . '%')
                        ->orWhere('nom->fr', 'like', '%' . $search . '%')
                        ->orWhere('nom->ar', 'like', '%' . $search . '%')
                        ->orWhere('numtelephone', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $data['Sections'] = Section::query()
            ->forSchool($schoolId)
            ->with(['classroom.schoolgrade'])
            ->orderBy('id')
            ->get();

        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('student.studentlist')],
        ];

        return view('admin.studentInfo', $data);
    }

    public function create()
    {
        $this->authorize('create', StudentInfo::class);
        $schoolId = $this->currentSchoolId();

        $data['School'] = School::query()
            ->when($schoolId, fn ($query) => $query->whereKey($schoolId))
            ->with('schoolgrades')
            ->orderBy('name_school')
            ->get();

        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('student.studentlist'), 'url' => route('Students.index')],
            ['label' => trans('main_sidebar.addstudent')],
        ];

        return view('admin.addStudentParent', $data);
    }

    public function store(StoreStudent $request)
    {
        $this->authorize('create', StudentInfo::class);
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $section = Section::query()
            ->forSchool($schoolId)
            ->findOrFail($request->section_id);

        $payload = $this->buildStudentEnrollmentPayloadAction->execute($request->all());
        $studentPayload = $payload['student'];
        $guardianPayload = $payload['guardian'];

        try {
            $this->enrollmentService->createStudent($studentPayload, $guardianPayload, $section);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('messages.success'));

        return redirect()->route('Students.create');
    }

    public function update(StoreStudent $request, $id)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        $student = StudentInfo::query()
            ->with(['user', 'parent.user', 'section'])
            ->findOrFail($id);
        $this->authorize('update', $student);

        $section = Section::query()
            ->forSchool($schoolId)
            ->findOrFail($request->section_id);

        try {
            $this->updateStudentEnrollmentAction->execute($student, $request->all(), $section);
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()])->withInput();
        }

        toastr()->success(trans('messages.Update'));

        return redirect()->route('Students.index');
    }

    public function destroy($id)
    {
        $schoolId = $this->currentSchoolId();

        $student = StudentInfo::query()
            ->with(['user', 'parent.students', 'parent.user', 'section'])
            ->findOrFail($id);
        $this->authorize('delete', $student);

        $this->deleteStudentEnrollmentAction->execute($student);

        toastr()->error(trans('messages.delete'));

        return redirect()->route('Students.index');
    }

    public function importExcel(ImportStudentsRequest $request)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();

        try {
            Excel::import(new StudentsImport($this->enrollmentService, $schoolId), $request->file('file'));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        toastr()->success('تم استيراد التلاميذ بنجاح');

        return redirect()->route('Students.index');
    }
}
