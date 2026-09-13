<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
     * Resolve the branch (school) filter chosen in list pages; null means all branches.
     */
    protected function branchFilterId(): ?int
    {
        return (int) request('branch_id') ?: null;
    }

    /**
     * All branches (schools) for the list-pages branch filter dropdown.
     *
     * تُستدعى في كل صفحة قائمة تقريباً وتتغيّر نادراً، لذا تُخزَّن مؤقتاً.
     * الإبطال يتم عبر App\Observers\SchoolObserver عند أي تعديل على الفروع.
     */
    protected function branchOptions(): Collection
    {
        return Cache::remember('lookup:branches', 3600, function () {
            return \App\Models\School\School::query()
                ->select(['id', 'name_school'])
                ->orderBy('name_school')
                ->get();
        });
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
