<?php

namespace App\Policies;

use App\Models\AgendaScolaire\Exames;
use App\Models\User;

class ExamePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Exames $exame): bool
    {
        if (!$user) {
            return true;
        }

        if (!$user->hasRole('admin')) {
            return true;
        }

        if (!$user->school_id) {
            return true;
        }

        $examSchoolId = $exame->classroom?->school_id;

        if (!$examSchoolId) {
            return false;
        }

        return (int) $examSchoolId === (int) $user->school_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Exames $exame): bool
    {
        return $this->canManageExame($user, $exame);
    }

    public function delete(User $user, Exames $exame): bool
    {
        return $this->canManageExame($user, $exame);
    }

    private function canManageExame(User $user, Exames $exame): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        $examSchoolId = $exame->classroom?->school_id;

        if (!$examSchoolId) {
            return false;
        }

        return (int) $examSchoolId === (int) $user->school_id;
    }
}
