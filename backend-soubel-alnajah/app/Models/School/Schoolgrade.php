<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;


class Schoolgrade extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['name_grade','notes'];

    protected $fillable=['school_id','name_grade','notes'];
   

    protected $table = 'schoolgrades';
    public $timestamps = true;

    // public function Classrooms()
    // {
    //     return $this->hasMany('App\Models\School\Classroom');
    // }

    public function school()
    {
        return $this->belongsTo(School::class,'school_id','id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class, 'grade_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }

}
