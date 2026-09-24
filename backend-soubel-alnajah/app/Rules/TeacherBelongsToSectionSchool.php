<?php

namespace App\Rules;

use App\Models\Inscription\Teacher;
use App\Models\School\Section;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * الأستاذ المختار يجب أن ينتمي لفرع القسم (يدرّس فيه أو حسابه فيه)،
 * حتى لا يُسند أستاذ من فرع آخر في جدول حصص هذا الفرع.
 */
class TeacherBelongsToSectionSchool implements ValidationRule
{
    public function __construct(private mixed $sectionId)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $schoolId = Section::query()->acrossSchools()->whereKey((int) $this->sectionId)->value('school_id');
        if (!$schoolId) {
            return; // القسم نفسه يُتحقَّق منه بقاعدته الخاصة.
        }

        $query = Teacher::query()->acrossSchools()->whereKey((int) $value);
        (new Teacher())->restrictToSchool($query, (int) $schoolId);

        if (!$query->exists()) {
            $fail(trans('timetable.validation.teacher_other_branch'));
        }
    }
}
