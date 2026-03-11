<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class StudentImportProgressService
{
    private const CACHE_PREFIX = 'student_import_progress:';
    private const TTL_SECONDS = 7200;

    public function initialize(string $token): array
    {
        $payload = [
            'token' => $token,
            'status' => 'pending',
            'message' => null,
            'total_rows' => 0,
            'processed_rows' => 0,
            'imported_rows' => 0,
            'section_updated_rows' => 0,
            'duplicate_rows' => 0,
            'skipped_rows' => 0,
            'not_added_rows' => 0,
            'auto_filled_fields' => 0,
            'issues_preview' => [],
            'latest_issue' => null,
            'started_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'completed_at' => null,
            'progress_percent' => 0,
        ];

        $this->put($token, $payload);

        return $payload;
    }

    public function running(string $token, array $data): array
    {
        $payload = $this->get($token) ?? $this->initialize($token);
        $payload = array_merge($payload, $data, [
            'status' => 'running',
            'updated_at' => now()->toIso8601String(),
        ]);
        $payload['not_added_rows'] = (int) ($payload['duplicate_rows'] ?? 0) + (int) ($payload['skipped_rows'] ?? 0);
        $payload['progress_percent'] = $this->calculatePercent(
            (int) ($payload['processed_rows'] ?? 0),
            (int) ($payload['total_rows'] ?? 0)
        );

        $this->put($token, $payload);

        return $payload;
    }

    public function complete(string $token, array $data): array
    {
        $payload = $this->get($token) ?? $this->initialize($token);
        $payload = array_merge($payload, $data, [
            'status' => 'completed',
            'message' => $data['message'] ?? 'Import completed.',
            'updated_at' => now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
        ]);
        $payload['not_added_rows'] = (int) ($payload['duplicate_rows'] ?? 0) + (int) ($payload['skipped_rows'] ?? 0);
        $payload['progress_percent'] = 100;

        $this->put($token, $payload);

        return $payload;
    }

    public function fail(string $token, string $message, array $data = []): array
    {
        $payload = $this->get($token) ?? $this->initialize($token);
        $payload = array_merge($payload, $data, [
            'status' => 'failed',
            'message' => $message,
            'updated_at' => now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
        ]);
        $payload['not_added_rows'] = (int) ($payload['duplicate_rows'] ?? 0) + (int) ($payload['skipped_rows'] ?? 0);
        $payload['progress_percent'] = $this->calculatePercent(
            (int) ($payload['processed_rows'] ?? 0),
            (int) ($payload['total_rows'] ?? 0)
        );

        $this->put($token, $payload);

        return $payload;
    }

    public function get(string $token): ?array
    {
        return Cache::get($this->cacheKey($token));
    }

    private function put(string $token, array $payload): void
    {
        Cache::put($this->cacheKey($token), $payload, self::TTL_SECONDS);
    }

    private function cacheKey(string $token): string
    {
        return self::CACHE_PREFIX . $token;
    }

    private function calculatePercent(int $processed, int $total): float
    {
        if ($total <= 0) {
            return 0;
        }

        return round(min(100, ($processed / $total) * 100), 1);
    }
}
