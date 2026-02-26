<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Translatable\HasTranslations;
use App\Models\Inscription\StudentInfo;


class School extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name_school'];

    protected $fillable=['Name'];
    protected $table = 'schools';
    public $timestamps = true;

    // علاقة المراحل الدراسية لجلب الاقسام المتعلقة بكل مرحلة

    public function schoolgrades()
    {
        return $this->hasMany(Schoolgrade::class,'school_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(StudentInfo::class, Section::class, 'school_id', 'section_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('id', $schoolId);
    }
}
