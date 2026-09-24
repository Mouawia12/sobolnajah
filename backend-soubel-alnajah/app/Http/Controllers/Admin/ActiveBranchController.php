<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CurrentSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * مبدّل الفروع للمدير العام: يحفظ «فرعاً نشطاً» في الجلسة فتُقيَّد كل الصفحات به
 * تلقائياً (كمدير فرع)، أو يمسحه للعودة إلى رؤية كل الفروع.
 */
class ActiveBranchController extends Controller
{
    public function update(Request $request)
    {
        // مدير الفرع مقيّد بفرعه دائماً ولا يبدّل.
        abort_if((bool) Auth::user()?->school_id, 403);

        $validated = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:schools,id'],
        ]);

        $branchId = $validated['branch_id'] ?? null;

        if ($branchId) {
            $request->session()->put(CurrentSchool::SESSION_KEY, (int) $branchId);
        } else {
            $request->session()->forget(CurrentSchool::SESSION_KEY);
        }

        toastr()->success(trans('main_header.branch_switched'));

        return redirect()->back();
    }
}
