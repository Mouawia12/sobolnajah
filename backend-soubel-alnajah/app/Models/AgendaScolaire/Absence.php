<?php

namespace App\Models\AgendaScolaire;
use App\Models\Inscription\StudentInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'date',
        'hour_1', 'hour_2', 'hour_3', 'hour_4',
        'hour_5', 'hour_6', 'hour_7', 'hour_8',
        'hour_9',
    ];


    public function student()
    {
        return $this->belongsTo(StudentInfo::class,'student_id','id');
    }
}
