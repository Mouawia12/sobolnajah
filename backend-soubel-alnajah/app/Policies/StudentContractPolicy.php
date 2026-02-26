<?php

namespace App\Policies;

use App\Models\Accounting\StudentContract;
use App\Models\User;

class StudentContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('accountant');
    }

    public function view(User $user, StudentContract $contract): bool
    {
        return $this->canAccess($user, $contract->school_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('accountant');
    }

    public function update(User $user, StudentContract $contract): bool
    {
        return $this->canAccess($user, $contract->school_id);
    }

    private function canAccess(User $user, int $schoolId): bool
    {
        if (!$user->hasRole('admin') && !$user->hasRole('accountant')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $user->school_id === (int) $schoolId;
    }
}
