<?php

namespace App\Policies;

use App\Models\Recruitment\JobPost;
use App\Models\User;

class JobPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, JobPost $jobPost): bool
    {
        return $this->canAccess($user, $jobPost->school_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, JobPost $jobPost): bool
    {
        return $this->canAccess($user, $jobPost->school_id);
    }

    public function delete(User $user, JobPost $jobPost): bool
    {
        return $this->canAccess($user, $jobPost->school_id);
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
