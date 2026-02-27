<?php

namespace App\Actions\Inscription;

use Illuminate\Support\Arr;

class BuildGuardianProfilePayloadAction
{
    public function __construct(private BuildLocalizedNameAction $buildLocalizedNameAction)
    {
    }

    public function execute(array $guardianData, ?string $phone): array
    {
        return array_filter([
            'prenomwali' => $this->buildLocalizedNameAction->execute(
                Arr::get($guardianData, 'first_name.fr'),
                Arr::get($guardianData, 'first_name.ar'),
                Arr::get($guardianData, 'first_name.en'),
                true
            ),
            'nomwali' => $this->buildLocalizedNameAction->execute(
                Arr::get($guardianData, 'last_name.fr'),
                Arr::get($guardianData, 'last_name.ar'),
                Arr::get($guardianData, 'last_name.en'),
                true
            ),
            'relationetudiant' => Arr::get($guardianData, 'relation'),
            'adressewali' => Arr::get($guardianData, 'address'),
            'wilayawali' => Arr::get($guardianData, 'wilaya'),
            'dayrawali' => Arr::get($guardianData, 'dayra'),
            'baladiawali' => Arr::get($guardianData, 'baladia'),
            'numtelephonewali' => $phone,
        ], fn ($value) => !is_null($value));
    }
}
