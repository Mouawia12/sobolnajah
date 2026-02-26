<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Inscription;

class CreateInscriptionAction
{
    public function execute(array $payload): Inscription
    {
        return Inscription::query()->create($payload);
    }
}
