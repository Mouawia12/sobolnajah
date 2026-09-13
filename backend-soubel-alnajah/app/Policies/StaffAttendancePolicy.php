<?php

namespace App\Policies;

use App\Models\HR\StaffAttendance;
use App\Models\User;

class StaffAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, ?StaffAttendance $attendance = null): bool
    {
        return $this->canAccess($user, $attendance);
    }

    public function update(User $user, ?StaffAttendance $attendance = null): bool
    {
        return $this->canAccess($user, $attendance);
    }

    private function canAccess(User $user, ?StaffAttendance $attendance = null): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$attendance || !$user->school_id) {
            return true;
        }

        return (int) $attendance->school_id === (int) $user->school_id;
    }
}
