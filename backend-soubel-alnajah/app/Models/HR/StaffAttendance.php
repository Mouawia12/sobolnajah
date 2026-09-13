<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StaffAttendance extends Model
{
    use HasFactory;

    // حالات الحضور اليومية
    public const ABSENT = 0;
    public const PRESENT = 1;
    public const LATE = 2;

    public const STATUSES = [self::ABSENT, self::PRESENT, self::LATE];

    // أنواع الأطراف المسموح بها في العلاقة متعددة الأشكال
    public const TYPE_TEACHER = 'teacher';
    public const TYPE_EMPLOYEE = 'employee';

    protected $table = 'staff_attendances';

    protected $fillable = [
        'school_id', 'staffable_type', 'staffable_id',
        'date', 'status', 'check_in', 'notes', 'recorded_by',
    ];

    // التاريخ يبقى سلسلة Y-m-d (بدون cast) ليطابق updateOrCreate والاستعلامات بدقّة.
    protected $casts = [
        'status' => 'integer',
    ];

    public function staffable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }
}
