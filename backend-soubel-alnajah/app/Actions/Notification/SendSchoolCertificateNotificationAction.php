<?php

namespace App\Actions\Notification;

use App\Models\User;
use App\Notifications\StudentSchoolCertificateNotification;

class SendSchoolCertificateNotificationAction
{
    public function execute(User $sender, int $targetUserId, string $year, string $nameFr, string $nameAr): void
    {
        $targetUser = User::query()->findOrFail($targetUserId);

        $sender->notify(new StudentSchoolCertificateNotification($targetUser, $year, $nameFr, $nameAr));
    }
}
