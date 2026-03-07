<?php

namespace App\Actions\Notification;

use App\Models\User;
use App\Notifications\StudentSchoolCertificateNotification;

class SendSchoolCertificateNotificationAction
{
    public function execute(User $sender, int $targetUserId, array $requestDetails, string $nameFr, string $nameAr): void
    {
        $targetUser = User::query()->findOrFail($targetUserId);

        $schoolAdmins = User::query()
            ->when($sender->school_id, fn ($query) => $query->where('school_id', $sender->school_id))
            ->get()
            ->filter(fn (User $user) => $user->hasRole('admin'));

        foreach ($schoolAdmins as $admin) {
            $admin->notify(new StudentSchoolCertificateNotification($targetUser, $requestDetails, $nameFr, $nameAr));
        }
    }
}
