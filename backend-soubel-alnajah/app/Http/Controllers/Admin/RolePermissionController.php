<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleMenuSection;
use App\Models\User;
use App\Services\MenuAccessService;
use App\Support\MenuCatalog;
use App\Support\RoleCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index()
    {
        // ضمان وجود الأدوار الأساسية دائماً (ثابتة وغير قابلة للحذف).
        RoleCatalog::ensureCoreRolesExist();

        $roles = Role::query()->orderBy('id')->get(['id', 'name', 'display_name']);
        $editableRoles = $roles->reject(fn ($r) => $r->name === 'admin')->values();

        $granted = RoleMenuSection::query()
            ->get(['role_id', 'section_key'])
            ->groupBy('role_id')
            ->map(fn ($rows) => $rows->pluck('section_key')->all());

        return view('admin.roles.index', [
            'notify' => $this->notifications(),
            'roles' => $roles,
            'editableRoles' => $editableRoles,
            'sections' => MenuCatalog::sections(),
            'granted' => $granted,
            'protectedRoles' => RoleCatalog::coreRoleNames(),
            'currentUserId' => Auth::id(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => trans('roles.title')],
            ],
        ]);
    }

    /* ============================== الأدوار ============================== */

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9_\-]+$/'],
        ]);

        $name = $validated['name'] ?: Str::slug($validated['display_name'], '_');
        $name = $name ?: 'role_' . Str::lower(Str::random(6));

        if (Role::query()->where('name', $name)->exists()) {
            return response()->json(['ok' => false, 'message' => trans('roles.name_taken')], 422);
        }

        $role = Role::create([
            'name' => $name,
            'display_name' => $validated['display_name'],
            'description' => $validated['display_name'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => trans('roles.role_created'),
            'role' => ['id' => $role->id, 'name' => $role->name, 'display_name' => $role->display_name],
        ]);
    }

    public function destroyRole(Role $role)
    {
        if (RoleCatalog::isCore($role->name)) {
            return response()->json(['ok' => false, 'message' => trans('roles.cannot_delete_core')], 422);
        }

        RoleMenuSection::query()->where('role_id', $role->id)->delete();
        $role->delete();
        MenuAccessService::bustCache();

        return response()->json(['ok' => true, 'message' => trans('roles.role_deleted')]);
    }

    public function savePermissions(Request $request)
    {
        $validSections = MenuCatalog::keys();
        $editableRoleIds = Role::query()->where('name', '!=', 'admin')->pluck('id')->all();
        $perms = (array) $request->input('perms', []);

        DB::transaction(function () use ($editableRoleIds, $perms, $validSections) {
            foreach ($editableRoleIds as $roleId) {
                $selected = array_values(array_intersect((array) ($perms[$roleId] ?? []), $validSections));

                RoleMenuSection::query()->where('role_id', $roleId)->delete();

                $keys = array_merge([MenuCatalog::CONFIGURED_MARKER], $selected);
                RoleMenuSection::query()->insert(array_map(fn ($key) => [
                    'role_id' => $roleId,
                    'section_key' => $key,
                    'created_at' => now(),
                    'updated_at' => now(),
                ], $keys));
            }
        });

        MenuAccessService::bustCache();

        return response()->json(['ok' => true, 'message' => trans('roles.permissions_saved')]);
    }

    /* ============================== المستخدمون ============================== */

    public function usersData(Request $request)
    {
        $schoolId = $this->currentSchoolId();
        $search = trim((string) $request->query('q'));

        $users = User::query()
            ->with(['roles:id,name', 'school:id,name_school'])
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('email', 'like', '%' . $search . '%')
                    ->orWhere('name->ar', 'like', '%' . $search . '%')
                    ->orWhere('name->fr', 'like', '%' . $search . '%');
            }))
            ->orderByDesc('id')
            ->paginate(15);

        return response()->json([
            'ok' => true,
            'users' => $users->getCollection()->map(fn (User $u) => [
                'id' => $u->id,
                'name' => (string) $u->name,
                'email' => $u->email,
                'school' => optional($u->school)->name_school,
                'roles' => $u->roles->pluck('name')->all(),
            ])->values(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function storeUser(Request $request)
    {
        if ($request->input('role') === '') {
            $request->merge(['role' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            // دور واحد فقط لكل مستخدم.
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => ['ar' => $validated['name'], 'fr' => $validated['name'], 'en' => $validated['name']],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'must_change_password' => true,
            'school_id' => $this->currentSchoolId(),
        ]);

        $role = $validated['role'] ?? null;
        $user->syncRoles($role ? [$role] : []);

        MenuAccessService::bustCache();

        return response()->json([
            'ok' => true,
            'message' => trans('roles.user_created'),
            'user' => [
                'id' => $user->id,
                'name' => $validated['name'],
                'email' => $user->email,
                'roles' => $role ? [$role] : [],
            ],
        ]);
    }

    public function updateUserRoles(Request $request, User $user)
    {
        $this->assertSameSchool($user);

        if ($request->input('role') === '') {
            $request->merge(['role' => null]);
        }

        $validated = $request->validate([
            // دور واحد فقط لكل مستخدم.
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $role = $validated['role'] ?? null;
        $user->syncRoles($role ? [$role] : []);
        MenuAccessService::bustCache();

        return response()->json(['ok' => true, 'message' => trans('roles.roles_updated')]);
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $this->assertSameSchool($user);

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:6', 'max:100'],
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
            'must_change_password' => true,
        ]);

        return response()->json(['ok' => true, 'message' => trans('roles.password_reset_done')]);
    }

    public function destroyUser(User $user)
    {
        $this->assertSameSchool($user);

        if ((int) $user->id === (int) Auth::id()) {
            return response()->json(['ok' => false, 'message' => trans('roles.cannot_delete_self')], 422);
        }

        $user->delete();

        return response()->json(['ok' => true, 'message' => trans('roles.user_deleted')]);
    }

    private function assertSameSchool(User $user): void
    {
        $schoolId = $this->currentSchoolId();
        if ($schoolId && (int) $user->school_id !== (int) $schoolId) {
            abort(404);
        }
    }
}
