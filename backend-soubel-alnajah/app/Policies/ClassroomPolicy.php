<?php

namespace App\Policies;

use App\Models\School\Classroom;
use App\Models\User;

class ClassroomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Classroom $classroom): bool
    {
        return $this->canAccessClassroom($user, $classroom);
    }

    public function delete(User $user, Classroom $classroom): bool
    {
        return $this->canAccessClassroom($user, $classroom);
    }

    private function canAccessClassroom(User $user, Classroom $classroom): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $classroom->school_id === (int) $user->school_id;
    }
}
