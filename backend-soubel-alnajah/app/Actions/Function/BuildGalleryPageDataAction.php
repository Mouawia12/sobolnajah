<?php

namespace App\Actions\Function;

use App\Models\AgendaScolaire\Gallery;

class BuildGalleryPageDataAction
{
    public function execute(): array
    {
        return [
            'Gallery' => Gallery::query()->get(),
        ];
    }
}
