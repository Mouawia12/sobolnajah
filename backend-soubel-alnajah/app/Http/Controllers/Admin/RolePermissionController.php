<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleMenuSection;
use App\Services\MenuAccessService;
use App\Support\MenuCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    /** أدوار أساسية لا تُحذف. */
    private const PROTECTED_ROLES = ['admin', 'teacher', 'student', 'guardian', 'accountant', 'supervisor', 'employee'];

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index()
    {
        $roles = Role::query()->orderBy('id')->get(['id', 'name', 'display_name']);

        // الأدوار القابلة للتحكم في الجدول (كل الأدوار عدا admin الذي يرى كل شيء دائماً).
        $editableRoles = $roles->reject(fn ($role) => $role->name === 'admin')->values();

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
            'protectedRoles' => self::PROTECTED_ROLES,
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => trans('roles.title')],
            ],
        ]);
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'name' => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9_\-]+$/'],
        ]);

        $name = $validated['name'] ?: Str::slug($validated['display_name'], '_');
        $name = $name ?: 'role_' . Str::random(6);

        if (Role::query()->where('name', $name)->exists()) {
            return back()->withErrors(['name' => trans('roles.name_taken')])->withInput();
        }

        Role::create([
            'name' => $name,
            'display_name' => $validated['display_name'],
            'description' => $validated['display_name'],
        ]);

        toastr()->success(trans('roles.role_created'));

        return redirect()->route('roles.index');
    }

    public function destroyRole(Role $role)
    {
        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->withErrors(['error' => trans('roles.cannot_delete_core')]);
        }

        RoleMenuSection::query()->where('role_id', $role->id)->delete();
        $role->delete();
        MenuAccessService::bustCache();

        toastr()->error(trans('roles.role_deleted'));

        return redirect()->route('roles.index');
    }

    /**
     * حفظ جدول الصلاحيات: لكل دور قابل للتحكم، أقسام السايدبار التي يراها.
     */
    public function savePermissions(Request $request)
    {
        $validSections = MenuCatalog::keys();

        $editableRoleIds = Role::query()->where('name', '!=', 'admin')->pluck('id')->all();
        $perms = (array) $request->input('perms', []);

        DB::transaction(function () use ($editableRoleIds, $perms, $validSections) {
            foreach ($editableRoleIds as $roleId) {
                $selected = array_values(array_intersect((array) ($perms[$roleId] ?? []), $validSections));

                RoleMenuSection::query()->where('role_id', $roleId)->delete();

                // نُخزّن العلامة دائماً كي يبقى الدور «مضبوطاً» حتى لو أُخفيت كل أقسامه.
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

        toastr()->success(trans('roles.permissions_saved'));

        return redirect()->route('roles.index');
    }
}
