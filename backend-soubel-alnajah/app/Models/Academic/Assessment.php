<?php

namespace App\Models\Academic;

use App\Models\Inscription\Teacher;
use App\Models\School\School;
use App\Models\School\Section;
use App\Models\Specialization\Specialization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;
    use \App\Models\Concerns\BelongsToSchool;

    public const TYPE_DEVOIR = 'devoir'; // فرض
    public const TYPE_EXAM = 'exam';     // امتحان

    public const TYPES = [self::TYPE_DEVOIR, self::TYPE_EXAM];

    protected $fillable = [
        'school_id', 'teacher_id', 'section_id', 'specialization_id',
        'title', 'type', 'term', 'max_mark', 'coefficient', 'date', 'created_by',
    ];

    protected $casts = [
        'max_mark' => 'decimal:2',
        'coefficient' => 'decimal:2',
        'term' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function specialization(): BelongsTo
    {
        return $this->belongsTo(Specialization::class, 'specialization_id');
    }

    public function marks(): HasMany
    {
        return $this->hasMany(AssessmentMark::class);
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }
}
