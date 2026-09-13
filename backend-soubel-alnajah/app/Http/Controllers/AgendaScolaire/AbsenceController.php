<?php

namespace App\Http\Controllers\AgendaScolaire;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsenceStatusRequest;

use App\Models\School\Section;
use App\Models\Inscription\StudentInfo;

use App\Models\AgendaScolaire\Absence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AbsenceController extends Controller
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
        $this->authorize('viewAny', Absence::class);
        $branchId = $this->branchFilterId();
        $search = trim((string) request('q'));
        $from = request('date_from');
        $to = request('date_to');
        $sectionId = request('section_id');

        $data['Absence'] = Absence::query()
            ->select([
                'id',
                'student_id',
                'date',
                'hour_1',
                'hour_2',
                'hour_3',
                'hour_4',
                'hour_5',
                'hour_6',
                'hour_7',
                'hour_8',
                'hour_9',
            ])
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('student.section', fn ($sectionQuery) => $sectionQuery->where('school_id', $branchId));
            })
            ->when($sectionId, function ($query) use ($sectionId) {
                $query->whereHas('student', fn ($studentQuery) => $studentQuery->where('section_id', $sectionId));
            })
            ->when($from, fn ($query) => $query->whereDate('date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('date', '<=', $to))
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('prenom', 'like', '%' . $search . '%')
                        ->orWhere('nom', 'like', '%' . $search . '%')
                        ->orWhere('numtelephone', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
                });
            })
            ->with([
                'student:id,section_id,prenom,nom,numtelephone',
                'student.section:id,name_section',
            ])
            ->orderBy('date', 'desc')
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
        $data['schools'] = $this->branchOptions();
        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('student.absence')],
        ];

        return view('admin.AbsenceStudent',$data);
     
    }




public function storeOrUpdate(StoreAbsenceStatusRequest $request)
{
    $this->authorize('create', Absence::class);

    // الكتابة مقيّدة بمدرسة المستخدم دائماً (فلتر الفروع يخصّ العرض فقط، لا الكتابة).
    $student = StudentInfo::query()
        ->forSchool($this->currentSchoolId())
        ->findOrFail((int) $request->student_id);

    // التاريخ اختياري — الصفحة الجديدة ترسل تاريخاً محدداً، المودال القديم يستعمل اليوم
    $date = $request->input('date') ?: now()->toDateString();

    $absence = Absence::firstOrCreate(
        ['student_id' => $student->id, 'date' => $date],
        ['hour_1' => Absence::PRESENT, 'hour_2' => Absence::PRESENT, 'hour_3' => Absence::PRESENT,
         'hour_4' => Absence::PRESENT, 'hour_5' => Absence::PRESENT, 'hour_6' => Absence::PRESENT,
         'hour_7' => Absence::PRESENT, 'hour_8' => Absence::PRESENT, 'hour_9' => Absence::PRESENT]
    );

    $this->authorize('update', $absence);

    // تحديث عمود مسموح فقط عبر whitelist من FormRequest.
    $absence->{$request->hour} = $request->status;
    $absence->save();

    return response()->json(['success' => true, 'absence' => $absence]);
}


    public function getToday(Request $request)
        {
            $studentId = $request->query('student_id');

            if (!$studentId) {
                return response()->json([], 200);
            }

            StudentInfo::query()
                ->findOrFail((int) $studentId);

            $absence = Absence::where('student_id', $studentId)
                ->where('date', date('Y-m-d'))
                ->first();

            if (!$absence) {
                // لا توجد بيانات لليوم — رجع مصفوفة فارغة حتى يعرف الـ JS أنه لا يوجد سجل
                return response()->json([]);
            }

            // طوّع الاستجابة لتكون خريطة hour_1..hour_9 => 0|1|2 (غائب|حاضر|متأخر)
            $data = [];
            for ($i = 1; $i <= 9; $i++) {
                $key = "hour_{$i}";
                $data[$key] = (int) ($absence->{$key} ?? Absence::PRESENT);
            }

            return response()->json($data);
        }


    /**
     * صفحة تسجيل الحضور الجديدة (تصميم الموبايل).
     */
    public function recordPage()
    {
        $this->authorize('viewAny', Absence::class);
        $branchId = $this->branchFilterId();

        $data['Sections'] = Section::query()
            ->forSchool($branchId)
            ->select(['id', 'classroom_id', 'name_section'])
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->orderBy('id')
            ->get();

        $data['schools'] = $this->branchOptions();

        // خريطة الساعات: 8:00 => hour_1 ... 16:00 => hour_9
        $hours = [];
        foreach (range(8, 16) as $h) {
            $hours["hour_" . ($h - 7)] = sprintf('%d:00', $h);
        }
        $data['hours'] = $hours;

        // الساعة الافتراضية = الساعة الحالية إن كانت ضمن الدوام، وإلا الأولى
        $nowHour = (int) now()->format('G');
        $data['defaultHourKey'] = ($nowHour >= 8 && $nowHour <= 16) ? 'hour_' . ($nowHour - 7) : 'hour_1';

        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('opt.attendance_record_title')],
        ];

        return view('admin.attendance_record', $data);
    }

    /**
     * بيانات تلاميذ القسم وحالات الحضور لتاريخ معيّن (JSON).
     */
    public function recordData(Request $request)
    {
        $this->authorize('viewAny', Absence::class);

        $validated = $request->validate([
            'section_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
        ]);

        // نطاق المدرسة: القسم وتلاميذه يجب أن يتبعوا مدرسة المستخدم.
        $schoolId = $this->currentSchoolId();
        $section = Section::query()->forSchool($schoolId)->findOrFail((int) $validated['section_id']);

        $students = StudentInfo::query()
            ->forSchool($schoolId)
            ->where('section_id', $section->id)
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get(['id', 'prenom', 'nom', 'national_id', 'numtelephone']);

        $absences = Absence::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereDate('date', $validated['date'])
            ->get()
            ->keyBy('student_id');

        $defaultHours = array_fill_keys(
            array_map(fn ($i) => "hour_{$i}", range(1, 9)),
            Absence::PRESENT
        );

        $payload = $students->map(function ($student) use ($absences, $defaultHours) {
            $absence = $absences->get($student->id);
            $hours = $defaultHours;
            if ($absence) {
                foreach (array_keys($hours) as $key) {
                    $hours[$key] = (int) $absence->{$key};
                }
            }

            return [
                'id' => $student->id,
                'name' => trim($student->prenom . ' ' . $student->nom),
                'subtitle' => $student->national_id ?: ($student->numtelephone ? '0' . $student->numtelephone : ''),
                'hours' => $hours,
                'has_record' => (bool) $absence,
            ];
        })->values();

        return response()->json([
            'students' => $payload,
            'total' => $students->count(),
            'recorded' => $absences->count(),
        ]);
    }

    /**
     * تسجيل جماعي: ضبط حالة ساعة واحدة لكل تلاميذ القسم في تاريخ معيّن.
     */
    public function bulkUpdate(Request $request)
    {
        $this->authorize('create', Absence::class);

        $validated = $request->validate([
            'section_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'hour' => ['required', 'in:hour_1,hour_2,hour_3,hour_4,hour_5,hour_6,hour_7,hour_8,hour_9'],
            'status' => ['required', 'integer', 'in:0,1,2'],
        ]);

        // نطاق المدرسة: الكتابة الجماعية مقيّدة بمدرسة المستخدم.
        $schoolId = $this->currentSchoolId();
        $section = Section::query()->forSchool($schoolId)->findOrFail((int) $validated['section_id']);

        $studentIds = StudentInfo::query()
            ->forSchool($schoolId)
            ->where('section_id', $section->id)
            ->pluck('id');

        $updated = 0;
        DB::transaction(function () use ($studentIds, $validated, &$updated) {
            $hour = $validated['hour'];
            $status = (int) $validated['status'];
            $date = $validated['date'];

            // التلاميذ الذين لهم سجل غياب في هذا التاريخ مسبقاً
            $existingIds = Absence::query()
                ->whereIn('student_id', $studentIds)
                ->where('date', $date)
                ->pluck('student_id');

            // تحديث الموجودين دفعة واحدة: نضبط الساعة المختارة فقط
            if ($existingIds->isNotEmpty()) {
                Absence::query()
                    ->whereIn('student_id', $existingIds)
                    ->where('date', $date)
                    ->update([$hour => $status]);
            }

            // إدراج من لا سجل له دفعة واحدة: كل الساعات «حاضر» عدا الساعة المختارة
            $missingIds = $studentIds->diff($existingIds);
            if ($missingIds->isNotEmpty()) {
                $now = now();
                $defaults = [];
                foreach (range(1, 9) as $h) {
                    $defaults['hour_' . $h] = Absence::PRESENT;
                }

                $rows = $missingIds->map(function ($studentId) use ($defaults, $hour, $status, $date, $now) {
                    return array_merge($defaults, [
                        'student_id' => $studentId,
                        'date' => $date,
                        $hour => $status,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                })->all();

                Absence::query()->insert($rows);
            }

            $updated = $studentIds->count();
        });

        return response()->json([
            'success' => true,
            'updated' => $updated,
            'recorded' => $studentIds->count(),
        ]);
    }

    /**
     * تقرير حضور/غياب تلميذ خلال فترة — صفحة طباعة بترويسة المدرسة.
     */
    public function studentReport(StudentInfo $student)
    {
        $data = $this->buildStudentReportData($student);
        $data['isPdf'] = false;
        $data['pdfUrl'] = route('absences.student.report.pdf', array_merge(
            ['student' => $data['student']->id],
            request()->only('date_from', 'date_to')
        ));

        return view('admin.attendance.student_report', $data);
    }

    /**
     * نفس التقرير كملف PDF بترويسة المدرسة.
     */
    public function studentReportPdf(StudentInfo $student)
    {
        $data = $this->buildStudentReportData($student);
        $data['isPdf'] = true;
        $data['pdfUrl'] = null;

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return redirect()->route('absences.student.report', array_merge(
                ['student' => $data['student']->id],
                request()->only('date_from', 'date_to')
            ));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ])
            ->loadView('admin.attendance.student_report', $data)
            ->setPaper('a4', 'portrait');

        $name = trim(($data['student']->prenom ?? '') . '-' . ($data['student']->nom ?? ''));

        return $pdf->download('attendance-' . ($name ?: $data['student']->id) . '.pdf');
    }

    /**
     * تجميع بيانات تقرير حضور التلميذ ضمن نطاق مدرسة المستخدم.
     */
    private function buildStudentReportData(StudentInfo $student): array
    {
        $this->authorize('viewAny', Absence::class);

        // نطاق المدرسة: التلميذ يجب أن يتبع مدرسة المستخدم.
        $student = StudentInfo::query()
            ->forSchool($this->currentSchoolId())
            ->with(['section.classroom.schoolgrade.school'])
            ->findOrFail($student->id);

        $from = request('date_from') ?: now()->startOfMonth()->toDateString();
        $to = request('date_to') ?: now()->toDateString();

        $absences = Absence::query()
            ->where('student_id', $student->id)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get();

        $rows = [];
        $summary = ['present' => 0, 'late' => 0, 'absent' => 0];

        foreach ($absences as $absence) {
            $present = $late = $absent = 0;
            foreach (range(1, 9) as $h) {
                $value = (int) ($absence->{'hour_' . $h} ?? Absence::PRESENT);
                if ($value === Absence::LATE) { $late++; }
                elseif ($value === Absence::ABSENT) { $absent++; }
                else { $present++; }
            }

            // حالة اليوم التمثيلية
            if ($absent > 0 && $present === 0) { $dayStatus = 'absent'; }
            elseif ($late > 0) { $dayStatus = 'late'; }
            elseif ($absent > 0) { $dayStatus = 'absent'; }
            else { $dayStatus = 'present'; }

            $summary[$dayStatus]++;

            $rows[] = [
                'date' => $absence->date,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'status' => $dayStatus,
            ];
        }

        return [
            'student' => $student,
            'rows' => $rows,
            'summary' => $summary,
            'from' => $from,
            'to' => $to,
            'notify' => $this->notifications(),
        ];
    }

}
