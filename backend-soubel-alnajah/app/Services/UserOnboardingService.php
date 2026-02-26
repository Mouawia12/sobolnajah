<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

class UserOnboardingService
{
    /**
     * Send a password setup/reset link to newly provisioned users.
     */
    public function dispatchPasswordSetupLink(User $user): bool
    {
        if (!$user->email) {
            return false;
        }

        try {
            $status = Password::broker()->sendResetLink([
                'email' => $user->email,
            ]);

            return $status === Password::RESET_LINK_SENT;
        } catch (Throwable $exception) {
            Log::warning('Failed to dispatch password setup link.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}
