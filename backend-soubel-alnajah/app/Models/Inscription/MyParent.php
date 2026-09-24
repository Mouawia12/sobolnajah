<?php

namespace App\Models\Inscription;
use App\Models\Concerns\BelongsToSchoolThroughRelations;
use App\Models\User;
use App\Models\Inscription\StudentInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;


class MyParent extends Model
{
    use HasFactory;
    use BelongsToSchoolThroughRelations;
    use HasTranslations;
    public $translatable = ['prenomwali','nomwali'];

    protected $guarded = [];

    protected $table = 'my_parents';
    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentInfo::class, 'parent_id');
    }

    /** الولي ينتمي للفرع إن كان له ابن فيه أو كان حسابه فيه (قد يكون له أبناء في فرعين). */
    public function restrictToSchool(Builder $query, int $schoolId): void
    {
        $query->where(function (Builder $builder) use ($schoolId) {
            $builder->whereHas('students.section', fn (Builder $q) => $q->where('sections.school_id', $schoolId))
                ->orWhereHas('user', fn (Builder $q) => $q->where('users.school_id', $schoolId));
        });
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->whereHas('students.section', function (Builder $sectionQuery) use ($schoolId) {
            $sectionQuery->where('school_id', $schoolId);
        });
    }

    /**
     * نطاق أوسع من forSchool: يشمل أيضاً الأولياء بلا أبناء المنتمين
     * للمدرسة عبر حساب المستخدم (users.school_id).
     */
    public function scopeBelongingToSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where(function (Builder $scopedQuery) use ($schoolId) {
            $scopedQuery->whereHas('students.section', function (Builder $sectionQuery) use ($schoolId) {
                $sectionQuery->where('school_id', $schoolId);
            })->orWhereHas('user', function (Builder $userQuery) use ($schoolId) {
                $userQuery->where('school_id', $schoolId);
            });
        });
    }
}
