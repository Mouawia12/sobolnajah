<?php

namespace App\Models\Concerns;

use App\Models\Scopes\RelatedSchoolScope;
use Illuminate\Database\Eloquent\Builder;

/**
 * عزل مركزي بين الفروع لنموذج بلا عمود فرع مباشر، يُحدَّد فرعه عبر علاقاته.
 * على النموذج تعريف دالة تضيف شرط الانتماء للفرع.
 */
trait BelongsToSchoolThroughRelations
{
    public static function bootBelongsToSchoolThroughRelations(): void
    {
        static::addGlobalScope(new RelatedSchoolScope());
    }

    abstract public function restrictToSchool(Builder $query, int $schoolId): void;

    /** استعلام يتجاوز عزل الفرع (لعمليات المدير العام عبر الفروع عند الحاجة). */
    public function scopeAcrossSchools($query)
    {
        return $query->withoutGlobalScope(RelatedSchoolScope::class);
    }
}
