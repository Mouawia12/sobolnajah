<?php

namespace App\Models\Scopes;

use App\Support\CurrentSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * نطاق عالمي يقيّد كل الاستعلامات على النماذج التابعة لفرع بالفرع الحالي تلقائياً.
 * يُطبَّق فقط عند وجود فرع محدّد (مدير فرع أو فرع نشط)؛ وإلا لا يقيّد شيئاً (مدير عام/طرفية).
 */
class SchoolScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $schoolId = CurrentSchool::id();

        if ($schoolId === null) {
            return;
        }

        $column = method_exists($model, 'getSchoolIdColumn')
            ? $model->getSchoolIdColumn()
            : 'school_id';

        $builder->where($model->getTable() . '.' . $column, $schoolId);
    }
}
