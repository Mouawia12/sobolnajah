<?php

namespace App\Actions\Notification;

use App\Models\User;
use App\Notifications\StudentSchoolCertificateNotification;

class SendSchoolCertificateNotificationAction
{
    public function execute(User $sender, int $targetUserId, array $requestDetails, string $nameFr, string $nameAr): void
    {
        $targetUser = User::query()->findOrFail($targetUserId);

        $sender->notify(new StudentSchoolCertificateNotification($targetUser, $requestDetails, $nameFr, $nameAr));
    }
}
