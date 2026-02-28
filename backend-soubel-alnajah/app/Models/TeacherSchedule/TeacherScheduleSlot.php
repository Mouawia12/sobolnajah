<?php

namespace App\Models\TeacherSchedule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherScheduleSlot extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(TeacherSchedule::class, 'teacher_schedule_id');
    }
}
