<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\OnboardingDeliveryFailedNotification;
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
            $this->notifySchoolAdminsOfDeliveryFailure($user, 'missing_email');
            return false;
        }

        try {
            $status = Password::broker()->sendResetLink([
                'email' => $user->email,
            ]);

            $isSent = $status === Password::RESET_LINK_SENT;

            if (!$isSent) {
                $this->notifySchoolAdminsOfDeliveryFailure($user, 'reset_link_not_sent');
            }

            return $isSent;
        } catch (Throwable $exception) {
            Log::warning('Failed to dispatch password setup link.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);

            $this->notifySchoolAdminsOfDeliveryFailure($user, 'dispatch_exception');

            return false;
        }
    }

    private function notifySchoolAdminsOfDeliveryFailure(User $targetUser, string $reason): void
    {
        if (!config('onboarding.notify_admins_on_failure', true) || !$targetUser->school_id) {
            return;
        }

        $admins = User::query()
            ->where('school_id', $targetUser->school_id)
            ->get()
            ->filter(fn (User $candidate) => $candidate->hasRole('admin'));

        foreach ($admins as $admin) {
            $admin->notify(new OnboardingDeliveryFailedNotification($targetUser, $reason));
        }
    }
}
