<?php

namespace App\Http\Controllers\Academic;

use App\Actions\Notification\NotifyMarksEnteredAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssessmentRequest;
use App\Http\Requests\StoreMarksRequest;
use App\Models\Academic\Assessment;
use App\Models\Academic\AssessmentMark;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\Teacher;
use App\Models\School\Section;
use App\Models\Specialization\Specialization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'force.password.change', 'role:admin|teacher']);
    }

    public function index()
    {
        $this->authorize('viewAny', Assessment::class);

        $teacher = $this->currentTeacher();
        $isTeacherOnly = $this->isTeacherOnly();

        $assessments = Assessment::query()
            ->with(['section:id,classroom_id,name_section', 'section.classroom:id,name_class', 'specialization:id,name'])
            ->withCount('marks')
            // أستاذ بلا سجل معلّم لا يرى شيئاً (بدون هذا الشرط كان يرى تقييمات كل المؤسسات).
            ->when($isTeacherOnly, fn ($q) => $q->where('teacher_id', $teacher?->id ?? 0))
            ->when(!$isTeacherOnly, fn ($q) => $q->forSchool($this->branchFilterId()))
            ->when(request('section_id'), fn ($q) => $q->where('section_id', (int) request('section_id')))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.academic.assessments.index', [
            'assessments' => $assessments,
            'sections' => $this->availableSections($teacher, $isTeacherOnly),
            'specializations' => Specialization::query()->select(['id', 'name'])->orderBy('name')->get(),
            'isTeacherOnly' => $isTeacherOnly,
            'teacherSpecializationId' => $teacher?->specialization_id,
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => $isTeacherOnly ? route('teacher.dashboard') : url('/admin')],
                ['label' => trans('academic.assessments')],
            ],
        ]);
    }

    public function store(StoreAssessmentRequest $request)
    {
        $this->authorize('create', Assessment::class);
        $validated = $request->validated();

        $teacher = $this->currentTeacher();
        $isTeacherOnly = $this->isTeacherOnly();

        // القسم يجب أن يكون ضمن نطاق المستخدم.
        $section = $this->availableSections($teacher, $isTeacherOnly)
            ->firstWhere('id', (int) $validated['section_id']);
        if (!$section) {
            return back()->withErrors(['section_id' => trans('academic.section_not_allowed')])->withInput();
        }

        // الأستاذ: مادته وهويته تُفرض تلقائياً؛ المسؤول يختار المادة.
        $teacherId = $isTeacherOnly ? $teacher?->id : null;
        $specializationId = $isTeacherOnly
            ? $teacher?->specialization_id
            : ($validated['specialization_id'] ?? null);

        Assessment::query()->create([
            'school_id' => (int) $section->school_id,
            'teacher_id' => $teacherId,
            'section_id' => $section->id,
            'specialization_id' => $specializationId,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'term' => $validated['term'] ?? null,
            'max_mark' => $validated['max_mark'] ?? 20,
            'coefficient' => $validated['coefficient'] ?? 1,
            'date' => $validated['date'] ?? now()->toDateString(),
            'created_by' => Auth::id(),
        ]);

        toastr()->success(trans('messages.success'));

        return redirect()->route('assessments.index');
    }

    public function marks(Assessment $assessment)
    {
        $this->authorize('view', $assessment);

        $students = $this->sectionStudents($assessment->section_id);
        $existing = $assessment->marks()->get()->keyBy('student_id');

        return view('admin.academic.assessments.marks', [
            'assessment' => $assessment->load(['section.classroom', 'specialization']),
            'students' => $students,
            'existing' => $existing,
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => $this->isTeacherOnly() ? route('teacher.dashboard') : url('/admin')],
                ['label' => trans('academic.assessments'), 'url' => route('assessments.index')],
                ['label' => trans('academic.enter_marks')],
            ],
        ]);
    }

    public function storeMarks(StoreMarksRequest $request, Assessment $assessment, NotifyMarksEnteredAction $notifyMarksEnteredAction)
    {
        $this->authorize('update', $assessment);
        $validated = $request->validated();

        $marks = $validated['mark'] ?? [];
        $absents = $validated['absent'] ?? [];
        $max = (float) $assessment->max_mark;

        $studentIds = $this->sectionStudents($assessment->section_id)->pluck('id')->all();
        $recorded = [];

        DB::transaction(function () use ($assessment, $marks, $absents, $max, $studentIds, &$recorded) {
            foreach ($studentIds as $studentId) {
                $isAbsent = (int) ($absents[$studentId] ?? 0) === 1;
                $rawMark = $marks[$studentId] ?? null;

                $mark = null;
                if (!$isAbsent && $rawMark !== null && $rawMark !== '') {
                    $mark = min((float) $rawMark, $max); // لا تتجاوز النقطة القصوى
                }

                // لا تنشئ صفاً فارغاً بلا نقطة وبلا غياب
                if ($mark === null && !$isAbsent) {
                    AssessmentMark::query()
                        ->where('assessment_id', $assessment->id)
                        ->where('student_id', $studentId)
                        ->delete();
                    continue;
                }

                AssessmentMark::query()->updateOrCreate(
                    ['assessment_id' => $assessment->id, 'student_id' => $studentId],
                    ['mark' => $mark, 'is_absent' => $isAbsent]
                );

                $recorded[] = $studentId;
            }
        });

        // إشعار كل تلميذ تم إدخال نقطته + ولي أمره (دون تكرار).
        $notifyMarksEnteredAction->execute($assessment->loadMissing('specialization'), $recorded);

        toastr()->success(trans('academic.marks_saved'));

        return redirect()->route('assessments.marks', $assessment);
    }

    public function destroy(Assessment $assessment)
    {
        $this->authorize('delete', $assessment);
        $assessment->delete();

        toastr()->error(trans('messages.delete'));

        return redirect()->route('assessments.index');
    }

    /**
     * كشف نقاط قسم/مادة: التلاميذ × التقييمات + المعدّل، طباعة/PDF بالترويسة.
     */
    public function results(Section $section)
    {
        $this->authorize('viewAny', Assessment::class);

        $teacher = $this->currentTeacher();
        $isTeacherOnly = $this->isTeacherOnly();

        // تحقّق من صلاحية الوصول للقسم.
        if (!$this->availableSections($teacher, $isTeacherOnly)->contains('id', $section->id)) {
            abort(404);
        }

        $specializationId = request('specialization_id') ?: ($isTeacherOnly ? $teacher?->specialization_id : null);
        $term = request('term');

        $assessments = Assessment::query()
            ->where('section_id', $section->id)
            ->when($isTeacherOnly && $teacher, fn ($q) => $q->where('teacher_id', $teacher->id))
            ->when($specializationId, fn ($q) => $q->where('specialization_id', (int) $specializationId))
            ->when($term, fn ($q) => $q->where('term', (int) $term))
            ->orderBy('date')->orderBy('id')
            ->get();

        $marksByAssessment = AssessmentMark::query()
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->groupBy('assessment_id');

        $students = $this->sectionStudents($section->id);

        $rows = $students->map(function ($student) use ($assessments, $marksByAssessment) {
            $cells = [];
            $weightedSum = 0.0;
            $coefSum = 0.0;

            foreach ($assessments as $assessment) {
                $mark = optional($marksByAssessment->get($assessment->id))->firstWhere('student_id', $student->id);
                if ($mark && !$mark->is_absent && $mark->mark !== null) {
                    $value = (float) $mark->mark;
                    $cells[$assessment->id] = number_format($value, 2);
                    $normalized = ((float) $assessment->max_mark) > 0 ? ($value / (float) $assessment->max_mark) * 20 : 0;
                    $weightedSum += $normalized * (float) $assessment->coefficient;
                    $coefSum += (float) $assessment->coefficient;
                } elseif ($mark && $mark->is_absent) {
                    $cells[$assessment->id] = trans('academic.absent_short');
                } else {
                    $cells[$assessment->id] = '—';
                }
            }

            $average = $coefSum > 0 ? round($weightedSum / $coefSum, 2) : null;

            return [
                'name' => trim(($student->prenom ?? '') . ' ' . ($student->nom ?? '')),
                'cells' => $cells,
                'average' => $average,
            ];
        });

        $data = [
            'section' => $section->load('classroom.schoolgrade.school'),
            'assessments' => $assessments,
            'rows' => $rows,
            'schoolName' => optional(optional(optional($section->classroom)->schoolgrade)->school)->name_school ?: trans('print.system_name'),
            'specialization' => $specializationId ? Specialization::find($specializationId) : null,
        ];

        if (request('format') === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $data['isPdf'] = true; $data['pdfUrl'] = null;
            return \Barryvdh\DomPDF\Facade\Pdf::setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true])
                ->loadView('admin.academic.assessments.results_print', $data)
                ->setPaper('a4', 'landscape')
                ->download('results.pdf');
        }

        $data['isPdf'] = false;
        $data['pdfUrl'] = route('assessments.results', array_merge(
            ['section' => $section->id],
            request()->only('specialization_id', 'term'),
            ['format' => 'pdf']
        ));

        return view('admin.academic.assessments.results_print', $data);
    }

    private function currentTeacher(): ?Teacher
    {
        return Teacher::query()->where('user_id', Auth::id())->first();
    }

    private function isTeacherOnly(): bool
    {
        $user = Auth::user();

        return $user && $user->hasRole('teacher') && !$user->hasRole('admin');
    }

    private function availableSections(?Teacher $teacher, bool $isTeacherOnly)
    {
        if ($isTeacherOnly) {
            if (!$teacher) {
                return collect();
            }

            return $teacher->sections()
                ->select(['sections.id', 'sections.school_id', 'sections.classroom_id', 'sections.name_section'])
                ->with(['classroom:id,name_class'])
                ->orderBy('sections.id')
                ->get();
        }

        return Section::query()
            ->forSchool($this->branchFilterId())
            ->select(['id', 'school_id', 'classroom_id', 'name_section'])
            ->with(['classroom:id,name_class'])
            ->orderBy('id')
            ->get();
    }

    private function sectionStudents(int $sectionId)
    {
        return StudentInfo::query()
            ->where('section_id', $sectionId)
            ->orderBy('nom')->orderBy('prenom')
            ->get(['id', 'prenom', 'nom', 'national_id']);
    }
}
