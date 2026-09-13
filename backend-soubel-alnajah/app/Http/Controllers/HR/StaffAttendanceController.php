<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkStaffAttendanceRequest;
use App\Http\Requests\StoreStaffAttendanceRequest;
use App\Models\HR\Employee;
use App\Models\HR\StaffAttendance;
use App\Models\Inscription\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * صفحة تسجيل حضور الموظفين اليومي بالكروت.
     */
    public function recordPage()
    {
        $this->authorize('viewAny', StaffAttendance::class);

        $data['schools'] = $this->branchOptions();
        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('hr.staff_attendance')],
        ];

        return view('admin.hr.staff_attendance.record', $data);
    }

    /**
     * قائمة الموظفين (أساتذة + موظفين) وحالة حضورهم لتاريخ معيّن (JSON).
     */
    public function recordData(Request $request)
    {
        $this->authorize('viewAny', StaffAttendance::class);

        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $schoolId = $this->branchFilterId();
        $date = $validated['date'];

        $staff = $this->staffList($schoolId);

        $attendances = StaffAttendance::query()
            ->whereDate('date', $date)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->get()
            ->keyBy(fn ($row) => $row->staffable_type . ':' . $row->staffable_id);

        $counts = ['present' => 0, 'late' => 0, 'absent' => 0, 'recorded' => 0];

        $payload = $staff->map(function (array $member) use ($attendances, &$counts) {
            $record = $attendances->get($member['type'] . ':' . $member['id']);
            $status = $record?->status;

            if ($record) {
                $counts['recorded']++;
                if ($status === StaffAttendance::PRESENT) { $counts['present']++; }
                elseif ($status === StaffAttendance::LATE) { $counts['late']++; }
                elseif ($status === StaffAttendance::ABSENT) { $counts['absent']++; }
            }

            return [
                'type' => $member['type'],
                'id' => $member['id'],
                'name' => $member['name'],
                'subtitle' => $member['subtitle'],
                'status' => $status,
                'has_record' => (bool) $record,
            ];
        })->values();

        return response()->json([
            'staff' => $payload,
            'total' => $payload->count(),
            'recorded' => $counts['recorded'],
            'present' => $counts['present'],
            'late' => $counts['late'],
            'absent' => $counts['absent'],
        ]);
    }

    /**
     * تسجيل/تحديث حالة حضور موظف واحد ليوم.
     */
    public function update(StoreStaffAttendanceRequest $request)
    {
        $this->authorize('create', StaffAttendance::class);
        $validated = $request->validated();

        $staffable = $this->resolveStaffable($validated['staff_type'], (int) $validated['staff_id']);
        $recordSchoolId = $this->resolveStaffSchoolId($staffable, $validated['staff_type']);

        $date = $validated['date'] ?? now()->toDateString();

        $attendance = StaffAttendance::query()->updateOrCreate(
            [
                'staffable_type' => $validated['staff_type'],
                'staffable_id' => $staffable->getKey(),
                'date' => $date,
            ],
            [
                'school_id' => $recordSchoolId,
                'status' => (int) $validated['status'],
                'check_in' => $validated['check_in'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => auth()->id(),
            ]
        );

        return response()->json(['success' => true, 'attendance' => $attendance]);
    }

    /**
     * تسجيل جماعي: ضبط حالة واحدة لكل موظفي الفرع في تاريخ معيّن.
     */
    public function bulkUpdate(BulkStaffAttendanceRequest $request)
    {
        $this->authorize('create', StaffAttendance::class);
        $validated = $request->validated();

        // الكتابة الجماعية تتطلب فرعاً محدّداً.
        $schoolId = $this->currentSchoolId() ?: $this->branchFilterId();
        if (!$schoolId) {
            return response()->json(['success' => false, 'message' => trans('hr.branch_required')], 422);
        }

        $date = $validated['date'] ?? now()->toDateString();
        $status = (int) $validated['status'];
        $staff = $this->staffList($schoolId);

        DB::transaction(function () use ($staff, $schoolId, $date, $status) {
            foreach ($staff as $member) {
                StaffAttendance::query()->updateOrCreate(
                    [
                        'staffable_type' => $member['type'],
                        'staffable_id' => $member['id'],
                        'date' => $date,
                    ],
                    [
                        'school_id' => $schoolId,
                        'status' => $status,
                        'recorded_by' => auth()->id(),
                    ]
                );
            }
        });

        return response()->json(['success' => true, 'recorded' => $staff->count()]);
    }

    /**
     * تقرير الحضور خلال فترة: ملخّص حاضر/متأخر/غائب لكل موظف.
     */
    public function report()
    {
        $this->authorize('viewAny', StaffAttendance::class);

        $data = $this->reportData();
        $data['schools'] = $this->branchOptions();
        $data['notify'] = $this->notifications();
        $data['breadcrumbs'] = [
            ['label' => 'لوحة التحكم', 'url' => url('/admin')],
            ['label' => trans('hr.staff_attendance'), 'url' => route('staff-attendance.record')],
            ['label' => trans('hr.attendance_report')],
        ];

        return view('admin.hr.staff_attendance.report', $data);
    }

    /**
     * نسخة الطباعة/الـ PDF من تقرير حضور الموظفين بترويسة المدرسة.
     */
    public function reportPrint()
    {
        $this->authorize('viewAny', StaffAttendance::class);

        $data = $this->reportData();
        $wantsPdf = request('format') === 'pdf';

        if ($wantsPdf && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $data['isPdf'] = true;
            $data['pdfUrl'] = null;

            return \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                    'defaultFont' => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                ])
                ->loadView('admin.hr.staff_attendance.report_print', $data)
                ->setPaper('a4', 'portrait')
                ->download('staff-attendance.pdf');
        }

        $data['isPdf'] = false;
        $data['pdfUrl'] = route('staff-attendance.report.print', array_merge(
            request()->only('branch_id', 'date_from', 'date_to'),
            ['format' => 'pdf']
        ));

        return view('admin.hr.staff_attendance.report_print', $data);
    }

    /**
     * تجميع بيانات تقرير الحضور خلال فترة.
     */
    private function reportData(): array
    {
        $schoolId = $this->branchFilterId();
        $from = request('date_from') ?: now()->startOfMonth()->toDateString();
        $to = request('date_to') ?: now()->toDateString();

        $staff = $this->staffList($schoolId);

        $records = StaffAttendance::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereBetween('date', [$from, $to])
            ->get(['staffable_type', 'staffable_id', 'status'])
            ->groupBy(fn ($row) => $row->staffable_type . ':' . $row->staffable_id);

        $rows = $staff->map(function (array $member) use ($records) {
            $group = $records->get($member['type'] . ':' . $member['id']) ?? collect();

            return [
                'type' => $member['type'],
                'name' => $member['name'],
                'subtitle' => $member['subtitle'],
                'present' => $group->where('status', StaffAttendance::PRESENT)->count(),
                'late' => $group->where('status', StaffAttendance::LATE)->count(),
                'absent' => $group->where('status', StaffAttendance::ABSENT)->count(),
                'total' => $group->count(),
            ];
        })->values();

        $schoolName = null;
        if ($schoolId) {
            $schoolName = optional(\App\Models\School\School::find($schoolId))->name_school;
        }

        return [
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'schoolName' => $schoolName ?: trans('print.system_name'),
        ];
    }

    /**
     * قائمة موحّدة للموظفين (أساتذة + موظفين) ضمن نطاق مدرسة.
     */
    private function staffList(?int $schoolId)
    {
        $teachers = Teacher::query()
            ->forSchool($schoolId)
            ->with('user:id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Teacher $teacher) => [
                'type' => StaffAttendance::TYPE_TEACHER,
                'id' => (int) $teacher->id,
                'name' => (string) $teacher->name,
                'subtitle' => trans('hr.teacher'),
            ]);

        $employees = Employee::query()
            ->forSchool($schoolId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'job_title'])
            ->map(fn (Employee $employee) => [
                'type' => StaffAttendance::TYPE_EMPLOYEE,
                'id' => (int) $employee->id,
                'name' => (string) $employee->name,
                'subtitle' => $employee->job_title ?: trans('hr.employee'),
            ]);

        return $teachers->concat($employees)->values();
    }

    private function resolveStaffable(string $type, int $id): Model
    {
        // الكتابة مقيّدة بمدرسة المستخدم (المسؤول العام بلا مدرسة يكتب لأي فرع).
        $schoolId = $this->currentSchoolId();

        if ($type === StaffAttendance::TYPE_TEACHER) {
            return Teacher::query()->forSchool($schoolId)->findOrFail($id);
        }

        return Employee::query()->forSchool($schoolId)->findOrFail($id);
    }

    private function resolveStaffSchoolId(Model $staffable, string $type): ?int
    {
        $ownSchoolId = $this->currentSchoolId();
        if ($ownSchoolId) {
            return (int) $ownSchoolId;
        }

        if ($type === StaffAttendance::TYPE_EMPLOYEE) {
            return (int) $staffable->school_id;
        }

        // أستاذ: المدرسة من حسابه أو أول قسم مرتبط به.
        $viaUser = $staffable->user?->school_id;
        if ($viaUser) {
            return (int) $viaUser;
        }

        return $staffable->sections()->value('school_id');
    }
}
