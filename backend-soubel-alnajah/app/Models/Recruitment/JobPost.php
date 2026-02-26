<?php

namespace App\Models\Recruitment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPost extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function (Builder $builder) {
                $builder->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function (Builder $builder) {
                $builder->whereNull('closed_at')->orWhere('closed_at', '>', now());
            });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
