<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;


class Classroom extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name_class'];

    protected $fillable=['school_id','grade_id','name_class'];
   

    protected $table = 'classrooms';
    public $timestamps = true;


    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function schoolgrade(): BelongsTo
    {
        return $this->belongsTo(Schoolgrade::class,'grade_id','id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'classroom_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }



}
