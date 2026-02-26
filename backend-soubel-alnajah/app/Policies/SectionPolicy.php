<?php

namespace App\Policies;

use App\Models\School\Section;
use App\Models\User;

class SectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Section $section): bool
    {
        return $this->canAccessSection($user, $section);
    }

    public function delete(User $user, Section $section): bool
    {
        return $this->canAccessSection($user, $section);
    }

    private function canAccessSection(User $user, Section $section): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $section->school_id === (int) $user->school_id;
    }
}
