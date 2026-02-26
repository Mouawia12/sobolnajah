<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Inscription;

class UpdateInscriptionStatusAction
{
    public function execute(Inscription $inscription, string $status): void
    {
        $inscription->update([
            'statu' => $status,
        ]);
    }
}
