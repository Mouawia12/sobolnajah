<?php

namespace App\Models\Inscription;
use App\Models\User;
use App\Models\Specialization\Specialization;
use App\Models\School\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;


class Teacher extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name'];

    protected $guarded = [];

    protected $table = 'teachers';
   public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class,'specialization_id','id');
    }

    // علاقة المعلمين مع الاقسام
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(Section::class,'teacher_section');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($schoolId) {
            $builder->whereHas('sections', function (Builder $sectionQuery) use ($schoolId) {
                $sectionQuery->where('school_id', $schoolId);
            })->orWhereHas('user', function (Builder $userQuery) use ($schoolId) {
                $userQuery->where('school_id', $schoolId);
            });
        });
    }
    
}
