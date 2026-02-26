<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'paid_on' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(StudentContract::class, 'contract_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(ContractInstallment::class, 'installment_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class, 'payment_id');
    }

    public function scopeForSchool(Builder $query, ?int $schoolId): Builder
    {
        if (!$schoolId) {
            return $query;
        }

        return $query->where('school_id', $schoolId);
    }
}
