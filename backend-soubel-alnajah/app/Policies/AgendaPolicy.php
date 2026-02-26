<?php

namespace App\Policies;

use App\Models\AgendaScolaire\Agenda;
use App\Models\User;

class AgendaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Agenda $agenda): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Agenda $agenda): bool
    {
        return $user->hasRole('admin');
    }
}
