<?php

namespace App\Models\TeacherSchedule;

use App\Models\Inscription\Teacher;
use App\Models\School\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherSchedule extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(TeacherScheduleSlot::class)->orderBy('slot_index');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TeacherScheduleEntry::class)->orderBy('day_of_week')->orderBy('slot_index');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
