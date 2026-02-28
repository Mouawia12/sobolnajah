<?php

namespace App\Policies;

use App\Models\TeacherSchedule\TeacherSchedule;
use App\Models\User;

class TeacherSchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, TeacherSchedule $teacherSchedule): bool
    {
        if ($user->hasRole('admin')) {
            return $this->isSameSchool($user, $teacherSchedule->school_id);
        }

        if ($user->hasRole('teacher')) {
            return (int) ($teacherSchedule->teacher?->user_id ?? 0) === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, TeacherSchedule $teacherSchedule): bool
    {
        return $user->hasRole('admin') && $this->isSameSchool($user, $teacherSchedule->school_id);
    }

    public function delete(User $user, TeacherSchedule $teacherSchedule): bool
    {
        return $user->hasRole('admin') && $this->isSameSchool($user, $teacherSchedule->school_id);
    }

    private function isSameSchool(User $user, int $schoolId): bool
    {
        if (!$user->school_id) {
            return true;
        }

        return (int) $user->school_id === (int) $schoolId;
    }
}
