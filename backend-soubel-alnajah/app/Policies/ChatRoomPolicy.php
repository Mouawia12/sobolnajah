<?php

namespace App\Policies;

use App\Models\Chat\ChatRoom;
use App\Models\User;

class ChatRoomPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ChatRoom $chatRoom): bool
    {
        return $chatRoom->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }
}

