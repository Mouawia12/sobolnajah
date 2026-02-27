<?php

namespace App\Actions\Publication;

use App\Models\AgendaScolaire\Gallery;
use App\Models\AgendaScolaire\Publication;

class DeletePublicationAction
{
    public function __construct(private PublicationImageManager $imageManager)
    {
    }

    public function execute(Publication $publication): void
    {
        $gallery = Gallery::query()->where('publication_id', $publication->id)->first();

        if ($gallery && $gallery->img_url) {
            $images = json_decode($gallery->img_url, true);
            foreach ((array) $images as $image) {
                $this->imageManager->delete((string) $image);
            }
            $gallery->delete();
        }

        $publication->delete();
    }
}

