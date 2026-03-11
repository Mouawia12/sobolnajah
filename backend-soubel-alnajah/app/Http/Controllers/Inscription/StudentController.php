<?php

namespace App\Http\Controllers\Inscription;

use App\Actions\Inscription\BuildStudentEnrollmentPayloadAction;
use App\Actions\Inscription\DeleteStudentEnrollmentAction;
use App\Actions\Inscription\UpdateStudentEnrollmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyStudentRequest;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\StoreStudent;
use App\Imports\StudentsImport;
use App\Models\Inscription\StudentInfo;
use App\Models\School\School;
use App\Models\School\Section;
use App\Services\StudentImportProgressService;
use App\Services\StudentEnrollmentService;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Throwable;

class StudentController extends Controller
{
    public function __construct(
        private StudentEnrollmentService $enrollmentService,
        private StudentImportProgressService $studentImportProgressService,
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
            ->select(['id', 'name_school'])
            ->orderBy('name_school')
            ->get();

        $data['StudentInfo'] = StudentInfo::query()
            ->forSchool($schoolId)
            ->with([
                'user:id,email',
                'section:id,classroom_id,name_section',
                'section.classroom:id,school_id,grade_id,name_class',
                'section.classroom.schoolgrade:id,school_id,name_grade',
                'section.classroom.schoolgrade.school:id,name_school',
                'section.classroom.sections:id,classroom_id,name_section',
            ])
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
            ->select(['id', 'classroom_id', 'name_section'])
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
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

    public function destroy(DestroyStudentRequest $request, $id)
    {
        $validated = $request->validated();
        $schoolId = $this->currentSchoolId();

        $student = StudentInfo::query()
            ->with(['user', 'parent.students', 'parent.user', 'section'])
            ->findOrFail((int) $validated['id']);
        $this->authorize('delete', $student);

        $this->deleteStudentEnrollmentAction->execute($student);

        toastr()->error(trans('messages.delete'));

        return redirect()->route('Students.index');
    }

    public function importExcel(ImportStudentsRequest $request)
    {
        $request->validated();

        $schoolId = $this->currentSchoolId();
        $importToken = trim((string) $request->input('import_token', ''));
        if ($importToken === '') {
            $importToken = Str::lower((string) Str::uuid());
        }
        $this->studentImportProgressService->initialize($importToken);

        $import = new StudentsImport(
            $this->enrollmentService,
            $schoolId,
            $this->studentImportProgressService,
            $importToken
        );

        try {
            Excel::import($import, $request->file('file'));
        } catch (ValidationException $exception) {
            $this->studentImportProgressService->fail($importToken, 'Validation error while importing students.', [
                'issues_preview' => [$exception->getMessage()],
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'token' => $importToken,
                    'message' => 'Validation error while importing students.',
                    'errors' => $exception->errors(),
                    'progress' => $this->studentImportProgressService->get($importToken),
                ], 422);
            }

            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            $this->studentImportProgressService->fail($importToken, $exception->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'token' => $importToken,
                    'message' => $exception->getMessage(),
                    'progress' => $this->studentImportProgressService->get($importToken),
                ], 500);
            }

            return back()->withErrors(['error' => $exception->getMessage()]);
        }

        $summary = [
            'total_rows' => $import->getTotalRows(),
            'processed_rows' => $import->getProcessedRows(),
            'imported_rows' => $import->getImportedRows(),
            'section_updated_rows' => $import->getSectionUpdatedRows(),
            'duplicate_rows' => $import->getDuplicateRows(),
            'skipped_rows' => $import->getSkippedRows(),
            'not_added_rows' => $import->getNotImportedRows(),
            'auto_filled_fields' => $import->getAutoFilledFields(),
        ];
        $issues = $import->getIssues();

        $this->studentImportProgressService->complete($importToken, [
            'message' => 'Student import completed successfully.',
            'total_rows' => $summary['total_rows'],
            'processed_rows' => $summary['processed_rows'],
            'imported_rows' => $summary['imported_rows'],
            'section_updated_rows' => $summary['section_updated_rows'],
            'duplicate_rows' => $summary['duplicate_rows'],
            'skipped_rows' => $summary['skipped_rows'],
            'not_added_rows' => $summary['not_added_rows'],
            'auto_filled_fields' => $summary['auto_filled_fields'],
            'issues_preview' => array_slice($issues, -5),
            'latest_issue' => !empty($issues) ? end($issues) : null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'token' => $importToken,
                'message' => 'Student import completed successfully.',
                'summary' => $summary,
                'issues' => $issues,
                'progress' => $this->studentImportProgressService->get($importToken),
            ]);
        }

        toastr()->success(
            sprintf(
                'تم الاستيراد بنجاح: %d صف مضاف، %d صف تم تصحيح قسمه، %d صف مكرر، %d صف متجاهل، %d صف غير مضاف، %d قيمة تم تعويضها تلقائيًا.',
                $summary['imported_rows'],
                $summary['section_updated_rows'],
                $summary['duplicate_rows'],
                $summary['skipped_rows'],
                $summary['not_added_rows'],
                $summary['auto_filled_fields']
            )
        );

        if (!empty($issues)) {
            $preview = implode(' | ', array_slice($issues, 0, 3));
            $remaining = count($issues) - 3;
            $suffix = $remaining > 0 ? " (+{$remaining} مشاكل إضافية)" : '';
            toastr()->warning('بعض الصفوف لم تُستورد: ' . $preview . $suffix);
        }

        return redirect()->route('Students.index');
    }

    public function importStatus(string $token)
    {
        $status = $this->studentImportProgressService->get($token);
        if (!$status) {
            return response()->json([
                'ok' => false,
                'message' => 'Import token not found.',
            ], 404);
        }

        return response()->json($status);
    }
}
