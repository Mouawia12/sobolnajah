<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(StudentContract::class, 'contract_id');
    }
}
