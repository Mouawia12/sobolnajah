<?php

namespace App\Models\Concerns;

use App\Models\Scopes\SchoolScope;
use App\Support\CurrentSchool;

/**
 * سمة العزل المركزي بين الفروع لكل نموذج يحمل عمود school_id.
 *
 * - تضيف نطاقاً عالمياً يقيّد كل الاستعلامات بالفرع الحالي تلقائياً.
 * - تملأ school_id تلقائياً عند الإنشاء إن كان المستخدم مقيّداً بفرع ولم يُحدَّد.
 *
 * بهذا لا يعتمد العزل على تذكّر المبرمج إضافة forSchool في كل استعلام.
 */
trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope());

        static::creating(function ($model) {
            $column = $model->getSchoolIdColumn();

            if (empty($model->{$column})) {
                $schoolId = CurrentSchool::id();
                if ($schoolId !== null) {
                    $model->{$column} = $schoolId;
                }
            }
        });
    }

    /** اسم عمود الفرع (يمكن تجاوزه في نموذج يستعمل اسماً مختلفاً). */
    public function getSchoolIdColumn(): string
    {
        return 'school_id';
    }

    /** استعلام يتجاوز عزل الفرع (لعمليات المدير العام عبر الفروع عند الحاجة). */
    public function scopeAcrossSchools($query)
    {
        return $query->withoutGlobalScope(SchoolScope::class);
    }
}
