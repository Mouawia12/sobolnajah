<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OnboardingDeliveryFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly User $targetUser,
        private readonly string $reason
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'target_user_id' => $this->targetUser->id,
            'target_user_email' => $this->targetUser->email,
            'target_user_school_id' => $this->targetUser->school_id,
            'reason' => $this->reason,
        ];
    }
}
