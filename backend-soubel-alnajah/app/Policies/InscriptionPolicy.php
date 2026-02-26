<?php

namespace App\Policies;

use App\Models\Inscription\Inscription;
use App\Models\User;

class InscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Inscription $inscription): bool
    {
        return $this->canAccess($user, $inscription);
    }

    public function create(?User $user): bool
    {
        // Public inscription form is allowed.
        return true;
    }

    public function update(User $user, Inscription $inscription): bool
    {
        return $this->canAccess($user, $inscription);
    }

    public function delete(User $user, Inscription $inscription): bool
    {
        return $this->canAccess($user, $inscription);
    }

    public function approve(User $user, Inscription $inscription): bool
    {
        return $this->canAccess($user, $inscription);
    }

    private function canAccess(User $user, Inscription $inscription): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $inscription->school_id === (int) $user->school_id;
    }
}
