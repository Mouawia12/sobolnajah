<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_group',
        'created_by',
    ];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_user')
            ->withPivot(['role', 'last_read_message_id', 'last_read_at'])
            ->withTimestamps();
    }

    public function participantStates(): HasMany
    {
        return $this->hasMany(ChatParticipant::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->whereHas('participants', fn (Builder $builder) => $builder->where('user_id', $userId));
    }

    public function ensureParticipant(User $user): void
    {
        $this->participants()->syncWithoutDetaching([$user->id]);
    }

    public function addParticipants(array $userIds): void
    {
        $this->participants()->syncWithoutDetaching($userIds);
    }

    public function otherParticipantThan(int $userId): ?User
    {
        if ($this->is_group) {
            return null;
        }

        return $this->participants->firstWhere('id', '!=', $userId);
    }
}
