<?php

namespace App\Models\Inscription;

use App\Models\Concerns\BelongsToSchoolThroughRelations;
use App\Models\User;
use App\Models\School\Section;
use App\Models\AgendaScolaire\NoteStudent;
use App\Models\Inscription\MyParent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Translatable\HasTranslations;

use Illuminate\Database\Eloquent\SoftDeletes;


class StudentInfo extends Model
{
    use SoftDeletes;
    use HasFactory;
    use BelongsToSchoolThroughRelations;
    use HasTranslations;
    public $translatable = ['prenom','nom'];

    protected $guarded = [];

    protected $casts = [
        'enrolled_at' => 'date',
        'born_by_judgment' => 'boolean',
    ];

    protected $table = 'studentinfos';
    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class,'section_id','id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MyParent::class, 'parent_id');
    }

    public function noteStudent(): HasOne
    {
        return $this->hasOne(NoteStudent::class, 'student_id');
    }

    /** فرع التلميذ هو فرع قسمه (يُشتقّ دائماً من القسم فلا يخرج عن التطابق). */
    public function restrictToSchool(Builder $query, int $schoolId): void
    {
        $query->whereHas('section', fn (Builder $q) => $q->where('sections.school_id', $schoolId));
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->whereHas('section', function (Builder $sectionQuery) use ($schoolId) {
            $sectionQuery->where('school_id', $schoolId);
        });
    }

}
