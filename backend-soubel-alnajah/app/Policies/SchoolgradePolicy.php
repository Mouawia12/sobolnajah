<?php

namespace App\Policies;

use App\Models\School\Schoolgrade;
use App\Models\User;

class SchoolgradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Schoolgrade $schoolgrade): bool
    {
        return $this->canAccessSchoolgrade($user, $schoolgrade);
    }

    public function delete(User $user, Schoolgrade $schoolgrade): bool
    {
        return $this->canAccessSchoolgrade($user, $schoolgrade);
    }

    private function canAccessSchoolgrade(User $user, Schoolgrade $schoolgrade): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $schoolgrade->school_id === (int) $user->school_id;
    }
}
