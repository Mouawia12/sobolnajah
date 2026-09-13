<?php

namespace App\Policies;

use App\Models\Academic\Assessment;
use App\Models\Inscription\Teacher;
use App\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('teacher');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('teacher');
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $this->canManage($user, $assessment);
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $this->canManage($user, $assessment);
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        return $this->canManage($user, $assessment);
    }

    private function canManage(User $user, Assessment $assessment): bool
    {
        // المسؤول: ضمن مدرسته (أو بلا مدرسة = عام).
        if ($user->hasRole('admin')) {
            return !$user->school_id || (int) $assessment->school_id === (int) $user->school_id;
        }

        // الأستاذ: يملك تقييماته فقط.
        if ($user->hasRole('teacher')) {
            $teacherId = Teacher::query()->where('user_id', $user->id)->value('id');

            return $teacherId && (int) $assessment->teacher_id === (int) $teacherId;
        }

        return false;
    }
}
