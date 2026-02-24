<?php

namespace App\Models\School;
use App\Models\Inscription\Teacher;
use App\Models\Inscription\StudentInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;


class Section extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name_section'];

    protected $guarded = [];
   

    protected $table = 'sections';
    public $timestamps = true;


    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class,'classroom_id','id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // علاقة الاقسام مع المعلمين
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class,'teacher_section');
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentInfo::class, 'section_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }
}
