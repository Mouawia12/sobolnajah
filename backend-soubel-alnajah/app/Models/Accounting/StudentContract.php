<?php

namespace App\Models\Accounting;

use App\Models\Inscription\StudentInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentContract extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentInfo::class, 'student_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(ContractInstallment::class, 'contract_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'contract_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }
}
