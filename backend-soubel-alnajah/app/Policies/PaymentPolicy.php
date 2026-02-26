<?php

namespace App\Policies;

use App\Models\Accounting\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('accountant');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->canAccess($user, $payment->school_id);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('accountant');
    }

    private function canAccess(User $user, int $schoolId): bool
    {
        if (!$user->hasRole('admin') && !$user->hasRole('accountant')) {
            return false;
        }

        if (!$user->school_id) {
            return true;
        }

        return (int) $user->school_id === (int) $schoolId;
    }
}
