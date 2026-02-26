<?php

namespace App\Actions\Notification;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarkUserNotificationAsReadAction
{
    public function execute(string $notificationId, int $authUserId): ?array
    {
        $notificationRow = DB::table('notifications')
            ->where('id', $notificationId)
            ->where('notifiable_id', $authUserId)
            ->first();

        if (!$notificationRow) {
            return null;
        }

        $user = User::query()->findOrFail($notificationRow->notifiable_id);
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return [
            'notifiable_id' => (int) $notificationRow->notifiable_id,
            'data' => $notificationRow->data,
        ];
    }
}
