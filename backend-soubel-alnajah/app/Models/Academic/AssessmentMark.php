<?php

namespace App\Models\Academic;

use App\Models\Inscription\StudentInfo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id', 'student_id', 'mark', 'is_absent', 'note',
    ];

    protected $casts = [
        'mark' => 'decimal:2',
        'is_absent' => 'boolean',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class, 'student_id');
    }
}
