<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Inscription;

class DeleteInscriptionAction
{
    public function execute(Inscription $inscription): void
    {
        $inscription->delete();
    }
}
