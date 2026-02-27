<?php

namespace App\Actions\Inscription;

class BuildLocalizedNameAction
{
    public function execute(
        ?string $fr = null,
        ?string $ar = null,
        ?string $en = null,
        bool $removeNullValues = false
    ): array {
        $name = [
            'fr' => $fr,
            'ar' => $ar,
            'en' => $en ?? $fr ?? $ar,
        ];

        if ($removeNullValues) {
            return array_filter($name, fn ($value) => !is_null($value));
        }

        return $name;
    }
}
