<?php

namespace App\Policies;

use App\Models\Inscription\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Teacher $teacher): bool
    {
        return $this->canAccessTeacher($user, $teacher);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Teacher $teacher): bool
    {
        return $this->canAccessTeacher($user, $teacher);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $this->canAccessTeacher($user, $teacher);
    }

    private function canAccessTeacher(User $user, Teacher $teacher): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        $teacherSchoolId = $teacher->user?->school_id
            ?? optional($teacher->sections()->first())->school_id;

        return (int) $teacherSchoolId === (int) $user->school_id;
    }
}
