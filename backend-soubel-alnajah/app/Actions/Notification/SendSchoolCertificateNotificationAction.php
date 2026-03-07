<?php

namespace App\Actions\Notification;

use App\Models\User;
use App\Notifications\StudentSchoolCertificateNotification;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class SendSchoolCertificateNotificationAction
{
    public function execute(User $sender, int $targetUserId, array $requestDetails, string $nameFr, string $nameAr): void
    {
        $targetUser = User::query()->findOrFail($targetUserId);

        $adminsQuery = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'));

        $schoolAdmins = $sender->school_id
            ? (clone $adminsQuery)->where('school_id', $sender->school_id)->get()
            : collect();

        $recipients = $schoolAdmins->isNotEmpty()
            ? $schoolAdmins
            : $adminsQuery->get();

        if ($recipients->isEmpty()) {
            throw new RuntimeException('No admin recipients available for certificate request notification.');
        }

        Notification::send(
            $recipients,
            new StudentSchoolCertificateNotification($targetUser, $requestDetails, $nameFr, $nameAr)
        );
    }
}
