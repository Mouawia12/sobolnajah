<?php

namespace App\Policies;

use App\Models\AgendaScolaire\Absence;
use App\Models\User;

class AbsencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, ?Absence $absence = null): bool
    {
        return $this->canAccessAbsence($user, $absence);
    }

    public function update(User $user, ?Absence $absence = null): bool
    {
        return $this->canAccessAbsence($user, $absence);
    }

    private function canAccessAbsence(User $user, ?Absence $absence = null): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$absence || !$user->school_id) {
            return true;
        }

        $studentSchoolId = optional(optional($absence->student)->section)->school_id;

        return (int) $studentSchoolId === (int) $user->school_id;
    }
}
