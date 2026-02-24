<?php

namespace App\Models\Inscription;
use App\Models\User;
use App\Models\Inscription\StudentInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;


class MyParent extends Model
{
    use HasFactory;
    use HasTranslations;
    public $translatable = ['prenomwali','nomwali'];

    protected $guarded = [];

    protected $table = 'my_parents';
    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentInfo::class, 'parent_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->whereHas('students.section', function (Builder $sectionQuery) use ($schoolId) {
            $sectionQuery->where('school_id', $schoolId);
        });
    }
}
