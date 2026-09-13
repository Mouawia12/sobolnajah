<?php

namespace App\Services;

use App\Models\Academic\Assessment;
use App\Models\Academic\AssessmentMark;

class StudentBulletinService
{
    /**
     * يبني كشف نقاط تلميذ: المواد وتقييماتها ومعدّل كل مادة + المعدّل العام.
     *
     * @return array{subjects: array<int, array>, general_average: float|null, assessments_count: int}
     */
    public function build(int $studentId, int $sectionId, ?int $term = null): array
    {
        $assessments = Assessment::query()
            ->where('section_id', $sectionId)
            ->when($term, fn ($q) => $q->where('term', (int) $term))
            ->with('specialization:id,name')
            ->orderBy('specialization_id')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        if ($assessments->isEmpty()) {
            return ['subjects' => [], 'general_average' => null, 'assessments_count' => 0];
        }

        $marks = AssessmentMark::query()
            ->where('student_id', $studentId)
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->keyBy('assessment_id');

        $subjects = [];

        foreach ($assessments->groupBy('specialization_id') as $specId => $group) {
            $subjectName = optional($group->first()->specialization)->name ?: trans('academic.subject');

            $items = [];
            $weightedSum = 0.0;
            $coefSum = 0.0;

            foreach ($group as $assessment) {
                $mark = $marks->get($assessment->id);
                $value = null;
                $absent = false;

                if ($mark && $mark->is_absent) {
                    $absent = true;
                } elseif ($mark && $mark->mark !== null) {
                    $value = (float) $mark->mark;
                    $max = (float) $assessment->max_mark;
                    $coef = (float) $assessment->coefficient;
                    $weightedSum += ($max > 0 ? ($value / $max) * 20 : 0) * $coef;
                    $coefSum += $coef;
                }

                $items[] = [
                    'title' => $assessment->title,
                    'type' => $assessment->type,
                    'max' => (float) $assessment->max_mark,
                    'coefficient' => (float) $assessment->coefficient,
                    'mark' => $value,
                    'absent' => $absent,
                ];
            }

            $subjects[] = [
                'subject' => (string) $subjectName,
                'items' => $items,
                'average' => $coefSum > 0 ? round($weightedSum / $coefSum, 2) : null,
            ];
        }

        $subjectAverages = array_values(array_filter(array_map(fn ($s) => $s['average'], $subjects), fn ($v) => $v !== null));
        $generalAverage = count($subjectAverages) > 0
            ? round(array_sum($subjectAverages) / count($subjectAverages), 2)
            : null;

        return [
            'subjects' => $subjects,
            'general_average' => $generalAverage,
            'assessments_count' => $assessments->count(),
        ];
    }
}
