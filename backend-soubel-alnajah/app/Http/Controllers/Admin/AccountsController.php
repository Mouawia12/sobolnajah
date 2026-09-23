<?php

namespace App\Http\Controllers\Admin;

use App\Actions\User\CreatePortalUserAction;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\School\School;
use App\Models\User;
use App\Support\RoleCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $search = trim((string) $request->query('q'));
        $roleFilter = trim((string) $request->query('role'));

        $users = User::query()
            ->with(['roles:id,name', 'school:id,name_school'])
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name->ar', 'like', '%' . $search . '%')
                    ->orWhere('name->fr', 'like', '%' . $search . '%');
            }))
            ->when($roleFilter !== '', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $roleFilter)))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.accounts.index', [
            'users' => $users,
            'roles' => $this->roleOptions(),
            'currentUserId' => Auth::id(),
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => trans('accounts.title')],
            ],
        ]);
    }

    /** الأدوار الأساسية أولاً (admin في المقدّمة) ثم المخصّصة: المعرّف => الاسم المعروض. */
    private function roleOptions(): array
    {
        $custom = Role::query()
            ->whereNotIn('name', RoleCatalog::coreRoleNames())
            ->orderBy('id')
            ->get(['name', 'display_name'])
            ->mapWithKeys(fn (Role $r) => [$r->name => $r->display_name ?: $r->name])
            ->all();

        return RoleCatalog::CORE_ROLES + $custom;
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->assertSameSchool($user);

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => true,
        ]);

        toastr()->success(trans('accounts.password_reset_done'));

        return redirect()->route('accounts.index');
    }

    public function updateRoles(Request $request, User $user, CreatePortalUserAction $createPortalUser)
    {
        $this->assertSameSchool($user);

        // منع المسؤول من تغيير دوره بنفسه حتى لا يفقد صلاحية الإدارة.
        if ((int) $user->id === (int) Auth::id()) {
            return back()->withErrors(['error' => trans('accounts.cannot_change_own_role')]);
        }

        if ($request->input('role') === '') {
            $request->merge(['role' => null]);
        }

        $validated = $request->validate([
            // دور واحد فقط لكل مستخدم.
            // الأدوار الأساسية والمخصّصة معاً (كان الدور المخصّص يُحذف عند الحفظ).
            'role' => ['nullable', 'string', 'in:' . implode(',', array_keys($this->roleOptions()))],
        ]);

        $role = $validated['role'] ?? null;
        if ($role) {
            Role::firstOrCreate(['name' => $role], ['display_name' => RoleCatalog::label($role)]);
        }

        DB::transaction(function () use ($user, $role, $createPortalUser) {
            $createPortalUser->ensureProfileForRole($user, $role);
            $user->syncRoles($role ? [$role] : []);
        });

        toastr()->success(trans('accounts.roles_updated'));

        return redirect()->route('accounts.index');
    }

    public function destroy(User $user)
    {
        $this->assertSameSchool($user);

        if ((int) $user->id === (int) Auth::id()) {
            return back()->withErrors(['error' => trans('accounts.cannot_delete_self')]);
        }

        $user->delete();

        toastr()->error(trans('accounts.account_deleted'));

        return redirect()->route('accounts.index');
    }

    /**
     * المسؤول المرتبط بمدرسة لا يدير إلا حسابات مدرسته.
     */
    private function assertSameSchool(User $user): void
    {
        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $user->school_id !== (int) $schoolId) {
            abort(404);
        }
    }
}
