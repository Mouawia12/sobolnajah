<?php

namespace App\Policies;

use App\Models\AgendaScolaire\Grade;
use App\Models\User;

class GradePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->hasRole('admin');
    }
}
