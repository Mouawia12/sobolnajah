<?php

namespace App\Policies;

use App\Models\Recruitment\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, JobApplication $jobApplication): bool
    {
        return $this->canAccess($user, $jobApplication->school_id);
    }

    public function update(User $user, JobApplication $jobApplication): bool
    {
        return $this->canAccess($user, $jobApplication->school_id);
    }

    public function downloadCv(User $user, JobApplication $jobApplication): bool
    {
        return $this->canAccess($user, $jobApplication->school_id);
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
