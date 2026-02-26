<?php

namespace App\Policies;

use App\Models\School\School;
use App\Models\User;

class SchoolPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        // School-bound admins cannot create new schools.
        return $user->hasRole('admin') && !$user->school_id;
    }

    public function update(User $user, School $school): bool
    {
        return $this->canAccessSchool($user, $school);
    }

    public function delete(User $user, School $school): bool
    {
        return $this->canAccessSchool($user, $school);
    }

    private function canAccessSchool(User $user, School $school): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $user->school_id === (int) $school->id;
    }
}
