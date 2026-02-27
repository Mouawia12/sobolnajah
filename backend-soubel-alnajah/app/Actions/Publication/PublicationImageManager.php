<?php

namespace App\Actions\Publication;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicationImageManager
{
    /**
     * @param  UploadedFile[]  $images
     * @return string[]
     */
    public function storeMany(array $images): array
    {
        $stored = [];

        foreach ($images as $image) {
            if (!$image instanceof UploadedFile) {
                continue;
            }

            $filename = time() . Str::random(rand(4, 10)) . '.' . $image->extension();
            Storage::disk('local')->putFileAs('private/publications', $image, $filename);
            $stored[] = $filename;
        }

        return $stored;
    }

    public function delete(string $filename): void
    {
        if ($filename === '') {
            return;
        }

        $localPath = 'private/publications/' . $filename;
        if (Storage::disk('local')->exists($localPath)) {
            Storage::disk('local')->delete($localPath);
        }

        Storage::disk('public')->delete('agenda/' . $filename);

        $legacyFile = public_path('agenda/' . $filename);
        if (file_exists($legacyFile)) {
            @unlink($legacyFile);
        }
    }
}

