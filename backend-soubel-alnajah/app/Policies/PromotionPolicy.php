<?php

namespace App\Policies;

use App\Models\Promotion\Promotion;
use App\Models\User;

class PromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, ?Promotion $promotion = null): bool
    {
        if (!$user->hasRole('admin')) {
            return false;
        }

        if (!$promotion || !$user->school_id) {
            return true;
        }

        return (int) $promotion->from_school === (int) $user->school_id;
    }
}
