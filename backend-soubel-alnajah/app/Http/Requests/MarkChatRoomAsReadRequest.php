<?php

namespace App\Http\Requests;

use App\Models\Chat\ChatRoom;
use Illuminate\Foundation\Http\FormRequest;

class MarkChatRoomAsReadRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $routeRoom = $this->route('room');
        $roomId = $routeRoom instanceof ChatRoom ? $routeRoom->id : $routeRoom;

        $this->merge([
            'room_id' => $roomId,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:chat_rooms,id'],
        ];
    }
}
