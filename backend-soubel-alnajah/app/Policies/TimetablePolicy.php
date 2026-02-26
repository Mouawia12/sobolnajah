<?php

namespace App\Policies;

use App\Models\Timetable\Timetable;
use App\Models\User;

class TimetablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Timetable $timetable): bool
    {
        return $this->canAccess($user, $timetable->school_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Timetable $timetable): bool
    {
        return $this->canAccess($user, $timetable->school_id);
    }

    public function delete(User $user, Timetable $timetable): bool
    {
        return $this->canAccess($user, $timetable->school_id);
    }

    private function canAccess(User $user, int $schoolId): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $user->school_id === (int) $schoolId;
    }
}
