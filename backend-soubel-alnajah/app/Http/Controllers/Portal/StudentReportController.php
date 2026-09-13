<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\User;
use App\Services\StudentBulletinService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class StudentReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'force.password.change']);
    }

    /**
     * كشف نقاط التلميذ (كل المواد + المعدّل العام) — للتلميذ والولي والمسؤول، مع تحميل PDF.
     */
    public function bulletin(Request $request, StudentInfo $student, StudentBulletinService $bulletinService)
    {
        $student->loadMissing(['user', 'parent', 'section.classroom.schoolgrade.school']);

        abort_unless($this->canAccessBulletin($request->user(), $student), 403);

        $term = $request->query('term');
        $bulletin = $bulletinService->build($student->id, (int) $student->section_id, $term ? (int) $term : null);

        $schoolName = optional(optional(optional($student->section)->classroom)->schoolgrade?->school)->name_school
            ?: trans('print.system_name');

        $data = [
            'student' => $student,
            'bulletin' => $bulletin,
            'term' => $term,
            'schoolName' => $schoolName,
        ];

        if ($request->query('format') === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $data['isPdf'] = true;
            $data['pdfUrl'] = null;

            $name = trim(($student->prenom ?? '') . '-' . ($student->nom ?? ''));

            return \Barryvdh\DomPDF\Facade\Pdf::setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true])
                ->loadView('front-end.bulletin', $data)
                ->setPaper('a4', 'portrait')
                ->download('bulletin-' . ($name ?: $student->id) . '.pdf');
        }

        $data['isPdf'] = false;
        $data['pdfUrl'] = route('reports.bulletin', array_merge(
            ['student' => $student->id],
            $request->only('term'),
            ['format' => 'pdf']
        ));

        return view('front-end.bulletin', $data);
    }

    private function canAccessBulletin(User $user, StudentInfo $student): bool
    {
        if ($user->hasRole('student')) {
            return (int) $student->user_id === (int) $user->id;
        }

        if ($user->hasRole('guardian')) {
            return $student->parent && (int) $student->parent->user_id === (int) $user->id;
        }

        if ($user->hasRole('admin')) {
            $studentSchoolId = optional($student->section)->school_id;

            return !$user->school_id || (int) $studentSchoolId === (int) $user->school_id;
        }

        return false;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user->hasRole('student') || $user->hasRole('guardian'), 403);

        $students = $user->hasRole('student')
            ? $this->studentRows($user->id)
            : $this->guardianRows($user->id);

        return view('front-end.reports', [
            'students' => $students,
            'reportColumns' => $this->reportColumns(),
        ]);
    }

    private function studentRows(int $userId): Collection
    {
        $student = StudentInfo::query()
            ->where('user_id', $userId)
            ->with(['user', 'section.classroom.schoolgrade', 'noteStudent'])
            ->first();

        return $student ? collect([$student]) : collect();
    }

    private function guardianRows(int $userId): Collection
    {
        $guardian = MyParent::query()
            ->where('user_id', $userId)
            ->with([
                'students.user',
                'students.section.classroom.schoolgrade',
                'students.noteStudent',
            ])
            ->first();

        return $guardian?->students ?? collect();
    }

    private function reportColumns(): array
    {
        return [
            ['key' => 'urlfile1', 'label' => 'الفصل الأول'],
            ['key' => 'urlfile2', 'label' => 'الفصل الثاني'],
            ['key' => 'urlfile3', 'label' => 'الفصل الثالث'],
        ];
    }
}
