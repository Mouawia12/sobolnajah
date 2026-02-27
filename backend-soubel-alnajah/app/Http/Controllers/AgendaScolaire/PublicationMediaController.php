<?php

namespace App\Http\Controllers\AgendaScolaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicationMediaController extends Controller
{
    public function show(Request $request, string $filename)
    {
        $this->assertSafeFilename($filename);

        $localPath = $this->localMediaPath($filename);
        if (Storage::disk('local')->exists($localPath)) {
            return Storage::disk('local')->response($localPath, null, [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        $this->migrateLegacyMediaIfExists($filename);
        if (Storage::disk('local')->exists($localPath)) {
            return Storage::disk('local')->response($localPath, null, [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        $legacyFile = public_path('agenda/' . $filename);
        if (file_exists($legacyFile)) {
            return response()->file($legacyFile, [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        abort(404);
    }

    private function localMediaPath(string $filename): string
    {
        return 'private/publications/' . $filename;
    }

    private function migrateLegacyMediaIfExists(string $filename): void
    {
        $legacyFile = public_path('agenda/' . $filename);
        if (!file_exists($legacyFile)) {
            return;
        }

        $localPath = $this->localMediaPath($filename);
        Storage::disk('local')->put($localPath, file_get_contents($legacyFile));
        @unlink($legacyFile);
    }

    private function assertSafeFilename(string $value): void
    {
        $isSafe = preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $value) === 1
            && !str_contains($value, '..')
            && !str_contains($value, '/')
            && !str_contains($value, '\\');

        if (!$isSafe) {
            abort(404);
        }
    }
}
