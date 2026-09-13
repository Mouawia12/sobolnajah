<?php

namespace App\Policies;

use App\Models\HR\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, ?Employee $employee = null): bool
    {
        return $this->canAccess($user, $employee);
    }

    public function update(User $user, ?Employee $employee = null): bool
    {
        return $this->canAccess($user, $employee);
    }

    public function delete(User $user, ?Employee $employee = null): bool
    {
        return $this->canAccess($user, $employee);
    }

    private function canAccess(User $user, ?Employee $employee = null): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$employee || !$user->school_id) {
            return true;
        }

        return (int) $employee->school_id === (int) $user->school_id;
    }
}
