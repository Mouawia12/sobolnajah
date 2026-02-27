<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateChatGroupRequest;
use App\Http\Requests\MarkChatRoomAsReadRequest;
use App\Http\Requests\SendChatMessageRequest;
use App\Http\Requests\StartDirectChatRequest;
use App\Models\Chat\ChatMessage;
use App\Models\Chat\ChatParticipant;
use App\Models\Chat\ChatRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private const AVAILABLE_USERS_LIMIT = 60;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ChatRoom::class);
        $currentUser = $request->user();
        $schoolId = $this->currentSchoolId();

        $rooms = ChatRoom::query()
            ->forUser($currentUser->id)
            ->with([
                'participants' => fn ($q) => $q->select('users.id', 'users.name'),
                'messages' => fn ($q) => $q->latest()->limit(1)->with('sender:id,name'),
            ])
            ->orderByDesc('updated_at')
            ->get();

        $availableUsers = User::query()
            ->whereKeyNot($currentUser->id)
            ->when($schoolId, fn (Builder $query) => $query->where('school_id', $schoolId))
            ->orderBy('name')
            ->limit(self::AVAILABLE_USERS_LIMIT)
            ->get(['id', 'name', 'email']);

        $activeRoomId = $request->integer('room') ?: null;

        return view('chat.index', [
            'rooms' => $rooms,
            'availableUsers' => $availableUsers,
            'activeRoomId' => $activeRoomId,
            'roomsJson' => $this->roomsPayload($rooms, $currentUser->id),
            'notify' => $this->notifications(),
        ]);
    }

    public function listRooms(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ChatRoom::class);
        $rooms = ChatRoom::query()
            ->forUser($request->user()->id)
            ->with([
                'participants:id,name',
                'messages' => fn ($q) => $q->latest()->limit(1)->with('sender:id,name'),
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json(
            $this->roomsPayload($rooms, $request->user()->id)
        );
    }

    public function messages(Request $request, ChatRoom $room): JsonResponse
    {
        $this->authorize('view', $room);

        $messages = $room->messages()
            ->with('sender:id,name')
            ->orderBy('created_at')
            ->get();

        ChatParticipant::query()
            ->where('chat_room_id', $room->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'last_read_at' => now(),
                'last_read_message_id' => optional($messages->last())->id,
            ]);

        return response()->json(
            $messages->map(fn (ChatMessage $message) => $this->messagePayload($message))
        );
    }

    public function sendMessage(SendChatMessageRequest $request, ChatRoom $room): JsonResponse
    {
        $this->authorize('view', $room);
        $data = $request->validated();

        $message = $room->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $room->touch();

        ChatParticipant::query()
            ->where('chat_room_id', $room->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'last_read_at' => now(),
                'last_read_message_id' => $message->id,
            ]);

        return response()->json($this->messagePayload($message->load('sender:id,name')));
    }

    public function startDirect(StartDirectChatRequest $request): JsonResponse
    {
        $this->authorize('create', ChatRoom::class);
        $data = $request->validated();

        $target = User::findOrFail($data['user_id']);
        $this->assertSameSchool($request->user(), $target);

        $room = $this->findOrCreateDirectRoom($request->user(), $target);

        $room->loadMissing([
            'participants:id,name',
            'messages' => fn ($q) => $q->latest()->limit(1)->with('sender:id,name'),
        ]);

        return response()->json([
            'room' => $this->roomPayload($room, $request->user()->id),
        ]);
    }

    public function createGroup(CreateChatGroupRequest $request): JsonResponse
    {
        $this->authorize('create', ChatRoom::class);
        $data = $request->validated();

        $members = User::whereIn('id', $data['members'])->get();
        foreach ($members as $member) {
            $this->assertSameSchool($request->user(), $member);
        }

        $room = ChatRoom::create([
            'name' => $data['name'],
            'is_group' => true,
            'created_by' => $request->user()->id,
        ]);

        $room->addParticipants(array_merge(
            [$request->user()->id],
            $members->pluck('id')->all()
        ));

        $room->loadMissing([
            'participants:id,name',
            'messages' => fn ($q) => $q->latest()->limit(1)->with('sender:id,name'),
        ]);

        return response()->json([
            'room' => $this->roomPayload($room, $request->user()->id),
        ], 201);
    }

    public function markRoomAsRead(MarkChatRoomAsReadRequest $request, $room): JsonResponse
    {
        $validated = $request->validated();
        $room = ChatRoom::query()->findOrFail((int) $validated['room_id']);
        $this->authorize('view', $room);

        $lastMessage = $room->messages()->latest()->first();

        ChatParticipant::query()
            ->where('chat_room_id', $room->id)
            ->where('user_id', $request->user()->id)
            ->update([
                'last_read_at' => now(),
                'last_read_message_id' => optional($lastMessage)->id,
            ]);

        return response()->json(['status' => 'ok']);
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $term = trim((string) $request->get('q', ''));

        if ($term === '') {
            return response()->json(['users' => []]);
        }

        $schoolId = $this->currentSchoolId();
        $lower = mb_strtolower($term, 'UTF-8');

        $users = User::query()
            ->whereKeyNot($request->user()->id)
            ->when($schoolId, fn (Builder $query) => $query->where('school_id', $schoolId))
            ->where(function (Builder $query) use ($lower) {
                $like = '%' . $lower . '%';
                $query->whereRaw('LOWER(JSON_EXTRACT(name, "$.\"fr\"")) like ?', [$like])
                    ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.\"ar\"")) like ?', [$like])
                    ->orWhereRaw('LOWER(JSON_EXTRACT(name, "$.\"en\"")) like ?', [$like]);
            })
            ->orderByDesc('created_at')
            ->limit(8)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'users' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $this->translated($user, 'name'),
                'email' => $user->email,
            ]),
        ]);
    }

    protected function findOrCreateDirectRoom(User $current, User $target): ChatRoom
    {
        $room = ChatRoom::query()
            ->where('is_group', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $current->id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $target->id))
            ->first();

        if ($room) {
            return $room;
        }

        $room = ChatRoom::create([
            'is_group' => false,
            'created_by' => $current->id,
        ]);

        $room->addParticipants([$current->id, $target->id]);

        return $room;
    }

    protected function assertSameSchool(User $a, User $b): void
    {
        if ($a->school_id && $b->school_id && $a->school_id !== $b->school_id) {
            abort(422, 'لا يمكن بدء محادثة مع مستخدم من مدرسة مختلفة.');
        }
    }

    protected function roomsPayload(Collection $rooms, int $currentUserId): Collection
    {
        $rooms->loadMissing([
            'participants:id,name',
            'messages' => fn ($q) => $q->latest()->limit(1)->with('sender:id,name'),
        ]);

        return $rooms->map(fn (ChatRoom $room) => $this->roomPayload($room, $currentUserId))->values();
    }

    protected function roomPayload(ChatRoom $room, int $currentUserId): array
    {
        $participants = $room->participants->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $this->translated($user, 'name'),
        ])->values();

        $otherNames = $participants->reject(fn (array $participant) => $participant['id'] === $currentUserId)->pluck('name');

        $displayName = $room->is_group && $room->name
            ? $room->name
            : ($otherNames->first() ?? $participants->first()['name'] ?? __('محادثة'));

        return [
            'id' => $room->id,
            'name' => $room->name,
            'display_name' => $displayName,
            'is_group' => (bool) $room->is_group,
            'participants' => $participants,
            'messages' => $room->messages->map(fn (ChatMessage $message) => $this->messagePayload($message))->values(),
        ];
    }

    protected function messagePayload(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'created_at' => optional($message->created_at)?->toISOString(),
            'user_id' => $message->user_id,
            'sender' => $message->sender ? [
                'id' => $message->sender->id,
                'name' => $this->translated($message->sender, 'name'),
            ] : null,
        ];
    }

    protected function translated($model, string $attribute): string
    {
        if (method_exists($model, 'getTranslation')) {
            return (string) $model->getTranslation($attribute, app()->getLocale());
        }

        $value = $model->{$attribute} ?? '';

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return (string) ($decoded[app()->getLocale()] ?? reset($decoded) ?? '');
            }

            return $value;
        }

        if (is_array($value)) {
            return (string) ($value[app()->getLocale()] ?? reset($value) ?? '');
        }

        return (string) $value;
    }
}
