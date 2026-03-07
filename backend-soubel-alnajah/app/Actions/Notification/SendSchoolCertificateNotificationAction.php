<?php

namespace App\Actions\Notification;

use App\Models\User;
use App\Notifications\StudentSchoolCertificateNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

class SendSchoolCertificateNotificationAction
{
    public function execute(User $sender, int $targetUserId, array $requestDetails, string $nameFr, string $nameAr): void
    {
        $targetUser = User::query()->findOrFail($targetUserId);

        $adminsQuery = User::query()
            ->whereHas('roles', function (Builder $query) {
                $query->whereRaw('LOWER(name) in (?, ?, ?, ?)', [
                    'admin',
                    'administrator',
                    'super-admin',
                    'super_admin',
                ]);
            });

        $schoolAdmins = $sender->school_id
            ? (clone $adminsQuery)->where('school_id', $sender->school_id)->get()
            : collect();

        $recipients = $schoolAdmins->isNotEmpty()
            ? $schoolAdmins
            : $adminsQuery->get();

        if ($recipients->isEmpty()) {
            // Last fallback for legacy setups where roles are inconsistent:
            // send to users who can access the admin dashboard URL pattern.
            $legacyRecipients = User::query()
                ->whereNotNull('email')
                ->orderBy('id')
                ->limit(5)
                ->get()
                ->filter(fn (User $user) => $user->hasRole('admin') || $user->hasRole('administrator'));

            if ($legacyRecipients->isEmpty()) {
                throw new RuntimeException('No admin recipients available for certificate request notification.');
            }

            $recipients = $legacyRecipients;
        }

        Notification::send(
            $recipients,
            new StudentSchoolCertificateNotification($targetUser, $requestDetails, $nameFr, $nameAr)
        );
    }
}
