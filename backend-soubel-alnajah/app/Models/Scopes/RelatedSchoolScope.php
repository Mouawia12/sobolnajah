<?php

namespace App\Models\Scopes;

use App\Support\CurrentSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * نطاق عالمي للنماذج التي قد تنتمي لأكثر من فرع (أستاذ يدرّس في فرعين، ولي له
 * أبناء في فرعين)، فلا يناسبها عمود فرع واحد. يقيّد الاستعلام بالفرع الحالي عبر
 * علاقات النموذج نفسه.
 */
class RelatedSchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $schoolId = CurrentSchool::id();

        if ($schoolId === null || CurrentSchool::isFamilyUser()) {
            return;
        }

        $model->restrictToSchool($builder, $schoolId);
    }
}
