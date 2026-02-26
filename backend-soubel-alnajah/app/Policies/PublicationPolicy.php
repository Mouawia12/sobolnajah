<?php

namespace App\Policies;

use App\Models\AgendaScolaire\Publication;
use App\Models\User;

class PublicationPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Publication $publication): bool
    {
        if (!$user) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return !$user->school_id || $publication->school_id === $user->school_id;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Publication $publication): bool
    {
        return $user->hasRole('admin')
            && (!$user->school_id || $publication->school_id === $user->school_id);
    }

    public function delete(User $user, Publication $publication): bool
    {
        return $user->hasRole('admin')
            && (!$user->school_id || $publication->school_id === $user->school_id);
    }
}
