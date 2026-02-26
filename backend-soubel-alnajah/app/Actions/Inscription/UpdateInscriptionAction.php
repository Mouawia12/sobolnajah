<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Inscription;

class UpdateInscriptionAction
{
    public function execute(Inscription $inscription, array $payload): void
    {
        $inscription->update($payload);
    }
}
