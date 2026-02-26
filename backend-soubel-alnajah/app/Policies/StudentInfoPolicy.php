<?php

namespace App\Policies;

use App\Models\Inscription\StudentInfo;
use App\Models\User;

class StudentInfoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, StudentInfo $studentInfo): bool
    {
        return $this->canAccessStudent($user, $studentInfo);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, StudentInfo $studentInfo): bool
    {
        return $this->canAccessStudent($user, $studentInfo);
    }

    public function delete(User $user, StudentInfo $studentInfo): bool
    {
        return $this->canAccessStudent($user, $studentInfo);
    }

    private function canAccessStudent(User $user, StudentInfo $studentInfo): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        $studentSchoolId = optional($studentInfo->section)->school_id;

        return (int) $studentSchoolId === (int) $user->school_id;
    }
}
