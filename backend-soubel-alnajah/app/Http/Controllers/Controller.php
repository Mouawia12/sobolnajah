<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Resolve the current authenticated user's school identifier.
     */
    protected function currentSchoolId(): ?int
    {
        $user = Auth::user();

        return $user?->school_id;
    }

    /**
     * Fetch the latest notifications shared across admin views.
     */
    protected function notifications(): Collection
    {
        $userId = Auth::id();
        if (!$userId) {
            return collect();
        }

        return DB::table('notifications')
            ->where('notifiable_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }
}
