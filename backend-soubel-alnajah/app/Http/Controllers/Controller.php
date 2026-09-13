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
     * Resolve the branch (school) filter for list pages.
     *
     * المسؤول المرتبط بمدرسة مقيّد بمدرسته دائماً ولا يتجاوزها عبر branch_id.
     * المسؤول العام (بلا مدرسة) يفلتر بحرية: branch_id محدّد أو null = كل الفروع.
     */
    protected function branchFilterId(): ?int
    {
        $ownSchoolId = Auth::user()?->school_id;
        if ($ownSchoolId) {
            return (int) $ownSchoolId;
        }

        return (int) request('branch_id') ?: null;
    }

    /**
     * All branches (schools) for the list-pages branch filter dropdown.
     *
     * المسؤول المرتبط بمدرسة يرى فرعه فقط في القائمة؛ المسؤول العام يرى كل الفروع.
     * قائمة الفروع تتغيّر نادراً لذا تُخزَّن مؤقتاً (يبطلها App\Observers\SchoolObserver).
     */
    protected function branchOptions(): Collection
    {
        $ownSchoolId = Auth::user()?->school_id;
        if ($ownSchoolId) {
            return \App\Models\School\School::query()
                ->whereKey($ownSchoolId)
                ->select(['id', 'name_school'])
                ->get();
        }

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
