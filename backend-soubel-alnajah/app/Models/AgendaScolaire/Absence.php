<?php

namespace App\Models\AgendaScolaire;
use App\Models\Inscription\StudentInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    // حالات الحضور لكل ساعة
    public const ABSENT = 0;
    public const PRESENT = 1;
    public const LATE = 2;

    protected $fillable = [
        'student_id', 'date',
        'hour_1', 'hour_2', 'hour_3', 'hour_4',
        'hour_5', 'hour_6', 'hour_7', 'hour_8',
        'hour_9',
    ];

    protected $casts = [
        'hour_1' => 'integer', 'hour_2' => 'integer', 'hour_3' => 'integer',
        'hour_4' => 'integer', 'hour_5' => 'integer', 'hour_6' => 'integer',
        'hour_7' => 'integer', 'hour_8' => 'integer', 'hour_9' => 'integer',
    ];


    public function student()
    {
        return $this->belongsTo(StudentInfo::class,'student_id','id');
    }
}
