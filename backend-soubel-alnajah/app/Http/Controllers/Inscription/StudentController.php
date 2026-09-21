<?php

namespace App\Http\Controllers\Inscription;

use App\Actions\Inscription\BuildStudentEnrollmentPayloadAction;
use App\Actions\Inscription\DeleteStudentEnrollmentAction;
use App\Actions\Inscription\UpdateStudentEnrollmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteBulkStudentsRequest;
use App\Http\Requests\DestroyStudentRequest;
use App\Http\Requests\ImportStudentsRequest;
use App\Http\Requests\StoreStudent;
use App\Models\Inscription\StudentInfo;
use App\Models\School\School;
use App\Models\School\Section;
use App\Services\MinistryStudentImportService;
use App\Services\StudentImportProgressService;
use App\Services\StudentEnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        $search = trim((string) request('q'));
        $sectionId = request('section_id');
        $classroomId = request('classroom_id');
        $gradeId = request('grade_id');
        $branchId = $this->branchFilterId();

        $data['School'] = $this->branchOptions();

        $data['StudentInfo'] = StudentInfo::query()
            ->forSchool($branchId)
            ->with([
                'user:id,email',
                'parent:id,prenomwali,nomwali,relationetudiant,adressewali,wilayawali,dayrawali,baladiawali,numtelephonewali,user_id',
                'parent.user:id,email',
                'parent.students:id,parent_id,prenom,nom',
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
                        ->orWhere('national_id', 'like', '%' . $search . '%')
                        ->orWhere('registration_number', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $data['Sections'] = Section::query()
            ->forSchool($branchId)
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

    public function printList()
    {
        $this->authorize('viewAny', StudentInfo::class);

        $branchId = $this->branchFilterId();
        $search = trim((string) request('q'));
        $sectionId = request('section_id');
        $classroomId = request('classroom_id');
        $gradeId = request('grade_id');

        $students = StudentInfo::query()
            ->forSchool($branchId)
            ->with([
                'section:id,classroom_id,name_section',
                'section.classroom:id,school_id,grade_id,name_class',
                'section.classroom.schoolgrade:id,school_id,name_grade',
                'section.classroom.schoolgrade.school:id,name_school',
            ])
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->when($classroomId, fn ($q) => $q->whereHas('section', fn ($s) => $s->where('classroom_id', $classroomId)))
            ->when($gradeId, fn ($q) => $q->whereHas('section.classroom', fn ($c) => $c->where('grade_id', $gradeId)))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($studentQuery) use ($search) {
                    $studentQuery->where('prenom->fr', 'like', '%' . $search . '%')
                        ->orWhere('prenom->ar', 'like', '%' . $search . '%')
                        ->orWhere('nom->fr', 'like', '%' . $search . '%')
                        ->orWhere('nom->ar', 'like', '%' . $search . '%')
                        ->orWhere('national_id', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('nom')
            ->limit(3000)
            ->get();

        $data = [
            'students' => $students,
            'schoolName' => $this->resolveBranchName($branchId),
        ];

        if (request('format') === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $data['isPdf'] = true;
            $data['pdfUrl'] = null;

            return \Barryvdh\DomPDF\Facade\Pdf::setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true])
                ->loadView('admin.students.print_list', $data)
                ->setPaper('a4', 'portrait')
                ->download('students.pdf');
        }

        $data['isPdf'] = false;
        $data['pdfUrl'] = route('students.print', array_merge(
            request()->only('branch_id', 'q', 'section_id', 'classroom_id', 'grade_id'),
            ['format' => 'pdf']
        ));

        return view('admin.students.print_list', $data);
    }

    private function resolveBranchName(?int $branchId): string
    {
        if ($branchId) {
            $school = School::find($branchId);
            if ($school) {
                return (string) $school->name_school;
            }
        }

        return trans('print.system_name');
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

    public function deleteAll(DeleteBulkStudentsRequest $request)
    {
        $this->authorize('viewAny', StudentInfo::class);
        $validated = $request->validated();

        $ids = collect(explode(',', $validated['delete_all_id']))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return redirect()->route('Students.index')
                ->withErrors(['delete_all_id' => trans('messages.cantdelete')]);
        }

        // مقيّد بمدرسة المستخدم الحالي حتى لا يمكن حذف تلاميذ مدرسة أخرى بحقن معرّفات.
        $students = StudentInfo::query()
            ->forSchool($this->currentSchoolId())
            ->with(['user', 'parent.students', 'parent.user', 'section'])
            ->whereIn('id', $ids)
            ->get();

        $deleted = 0;
        DB::transaction(function () use ($students, &$deleted) {
            foreach ($students as $student) {
                $this->authorize('delete', $student);
                $this->deleteStudentEnrollmentAction->execute($student);
                $deleted++;
            }
        });

        if ($deleted > 0) {
            toastr()->error(trans('messages.delete'));
        }

        return redirect()->route('Students.index');
    }

    /** أعمدة قالب الاستيراد البسيط: العنوان العربي => مفتاح الحقل. */
    private const TEMPLATE_COLUMNS = [
        'رقم التعريف' => 'national_id',
        'اللقب' => 'last_name_ar',
        'الاسم' => 'first_name_ar',
        'الجنس' => 'gender',
        'تاريخ الازدياد' => 'birth_date',
        'مكان الازدياد' => 'birth_place',
        'السنة' => 'grade',
        'الشعبة' => 'stream',
        'القسم' => 'section',
        'نظام التمدرس' => 'schooling_system',
        'رقم القيد' => 'registration_number',
        'تاريخ التسجيل' => 'enrolled_at',
    ];

    private const TEMPLATE_MAIN_SHEET = 'التلاميذ';

    /**
     * تنزيل قالب Excel فارغ لاستيراد التلاميذ، مع ورقة «مثال» توضيحية.
     */
    public function downloadImportTemplate()
    {
        $this->authorize('create', StudentInfo::class);

        $headers = array_keys(self::TEMPLATE_COLUMNS);
        $example = [
            '1234567890123456', 'بن علي', 'أحمد', 'ذكر', '2012-09-15', 'الجزائر',
            'السنة الأولى متوسط', 'جذع مشترك', 'القسم أ', 'خارجي', '2024-001', '2024-09-05',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ورقة التعبئة (التلاميذ): الرؤوس فقط
        $main = $spreadsheet->getActiveSheet();
        $main->setTitle(self::TEMPLATE_MAIN_SHEET);
        $main->setRightToLeft(true);
        $main->fromArray($headers, null, 'A1');
        $main->getStyle('A1:' . $main->getHighestColumn() . '1')->getFont()->setBold(true);
        foreach (range('A', $main->getHighestColumn()) as $col) {
            $main->getColumnDimension($col)->setWidth(20);
        }

        // ورقة المثال: الرؤوس + صف مثال (لا تُستورَد)
        $sample = $spreadsheet->createSheet();
        $sample->setTitle('مثال');
        $sample->setRightToLeft(true);
        $sample->fromArray($headers, null, 'A1');
        $sample->fromArray($example, null, 'A2');
        $sample->getStyle('A1:' . $sample->getHighestColumn() . '1')->getFont()->setBold(true);
        foreach (range('A', $sample->getHighestColumn()) as $col) {
            $sample->getColumnDimension($col)->setWidth(20);
        }
        $sample->setCellValue('A4', 'ملاحظة: عبّئ بياناتك في ورقة «التلاميذ». رقم التعريف يجب أن يكون 16 رقماً. الجنس: ذكر/أنثى.');

        $spreadsheet->setActiveSheetIndex(0);

        $fileName = 'قالب-استيراد-التلاميذ.xlsx';
        $tmp = tempnam(sys_get_temp_dir(), 'tpl') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tmp);

        return response()->download($tmp, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * استيراد التلاميذ من قالب Excel المعبّأ (ورقة «التلاميذ» فقط).
     */
    public function importTemplate(Request $request, MinistryStudentImportService $ministryImportService)
    {
        $this->authorize('create', StudentInfo::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
        ]);

        $schoolId = $this->currentSchoolId() ?: (int) $request->input('school_id');
        $school = $schoolId ? School::find($schoolId) : null;
        if (!$school) {
            return back()->withErrors(['school_id' => 'يجب تحديد المدرسة قبل الاستيراد.']);
        }

        try {
            $rows = $this->readTemplateRows($request->file('file')->getRealPath());
        } catch (Throwable $exception) {
            return back()->withErrors(['file' => 'تعذّر قراءة الملف: ' . $exception->getMessage()]);
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'لا توجد بيانات في ورقة «التلاميذ».']);
        }

        $token = Str::lower((string) Str::uuid());
        $this->studentImportProgressService->initialize($token);
        $summary = $ministryImportService->importRows($rows, $school, true, $token);
        $this->studentImportProgressService->complete($token, ['message' => 'تم الاستيراد']);

        toastr()->success(sprintf(
            'تم الاستيراد: %d مضاف، %d محدّث، %d نُقل قسمه، %d دون تغيير، %d فشل.',
            $summary['created_rows'], $summary['updated_rows'], $summary['moved_rows'],
            $summary['unchanged_rows'], $summary['failed_rows']
        ));

        if (!empty($summary['issues'])) {
            toastr()->warning('بعض الصفوف لم تُستورد: ' . implode(' | ', array_slice($summary['issues'], 0, 3)));
        }

        return redirect()->route('Students.index');
    }

    /**
     * يقرأ ورقة «التلاميذ» من ملف xlsx ويحوّلها لصفوف بمفاتيح الحقول.
     *
     * @return array<int, array<string, string>>
     */
    private function readTemplateRows(string $path): array
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName(self::TEMPLATE_MAIN_SHEET) ?? $spreadsheet->getSheet(0);
        $matrix = $sheet->toArray(null, true, false, false);

        if (count($matrix) < 2) {
            return [];
        }

        $headerRow = array_shift($matrix);
        $map = [];
        foreach ($headerRow as $index => $title) {
            $key = self::TEMPLATE_COLUMNS[trim((string) $title)] ?? null;
            if ($key) {
                $map[$index] = $key;
            }
        }

        $rows = [];
        foreach ($matrix as $line) {
            $row = [];
            $hasData = false;
            foreach ($map as $index => $key) {
                $value = trim((string) ($line[$index] ?? ''));
                $row[$key] = $value;
                if ($value !== '') {
                    $hasData = true;
                }
            }
            if ($hasData) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function importExcel(ImportStudentsRequest $request, MinistryStudentImportService $ministryImportService)
    {
        $request->validated();

        $importToken = trim((string) $request->input('import_token', ''));
        if ($importToken === '') {
            $importToken = Str::lower((string) Str::uuid());
        }
        $this->studentImportProgressService->initialize($importToken);

        try {
            $summary = $ministryImportService->import(
                $request->file('file')->getRealPath(),
                $importToken
            );
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

        $issues = $summary['issues'] ?? [];

        $this->studentImportProgressService->complete($importToken, [
            'message' => 'تمت مزامنة ملف التلاميذ بنجاح.',
            'school_name' => $summary['school_name'],
            'total_rows' => $summary['total_rows'],
            'processed_rows' => $summary['processed_rows'],
            'created_rows' => $summary['created_rows'],
            'updated_rows' => $summary['updated_rows'],
            'moved_rows' => $summary['moved_rows'],
            'unchanged_rows' => $summary['unchanged_rows'],
            'failed_rows' => $summary['failed_rows'],
            'structure_created' => $summary['structure_created'],
            'absent_count' => $summary['absent_count'],
            'absent_preview' => $summary['absent_preview'],
            'legacy_count' => $summary['legacy_count'],
            'issues_preview' => array_slice($issues, -5),
            'latest_issue' => !empty($issues) ? end($issues) : null,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'token' => $importToken,
                'message' => 'تمت مزامنة ملف التلاميذ بنجاح.',
                'summary' => $summary,
                'issues' => $issues,
                'progress' => $this->studentImportProgressService->get($importToken),
            ]);
        }

        toastr()->success(
            sprintf(
                'تمت مزامنة "%s": %d مضاف، %d محدّث، %d نُقل قسمه، %d دون تغيير، %d فشل، %d غائب عن الملف.',
                $summary['school_name'],
                $summary['created_rows'],
                $summary['updated_rows'],
                $summary['moved_rows'],
                $summary['unchanged_rows'],
                $summary['failed_rows'],
                $summary['absent_count']
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
