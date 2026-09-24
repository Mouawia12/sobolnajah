<?php

namespace App\Http\Controllers\Admin;

use App\Actions\User\CreatePortalUserAction;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleMenuSection;
use App\Models\School\Section;
use App\Models\Specialization\Specialization;
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

        $schoolId = $this->currentSchoolId();

        // بيانات المودل المنبثق لإنشاء مستخدم بدور واحد + ملف حسب الدور.
        $specializations = Specialization::query()->select(['id', 'name'])->orderBy('name')->get();

        $classSections = Section::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->select(['id', 'school_id', 'classroom_id', 'name_section'])
            ->orderBy('id')
            ->get();

        $guardians = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'guardian'))
            ->whereHas('parentProfile')
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with('parentProfile:id,user_id')
            ->select(['id', 'name', 'email', 'school_id'])
            ->orderByDesc('id')
            ->get();

        return view('admin.roles.index', [
            'notify' => $this->notifications(),
            'roles' => $roles,
            'editableRoles' => $editableRoles,
            'sections' => MenuCatalog::sections(),
            'granted' => $granted,
            'protectedRoles' => RoleCatalog::coreRoleNames(),
            'coreRoleNames' => RoleCatalog::coreRoleNames(),
            'specializations' => $specializations,
            'classSections' => $classSections,
            'guardians' => $guardians,
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

    public function storeUser(Request $request, CreatePortalUserAction $createPortalUser)
    {
        if ($request->input('role') === '') {
            $request->merge(['role' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            // دور واحد فقط لكل مستخدم.
            'role' => ['nullable', 'string', 'exists:roles,name'],
            // بيانات المعلّم (اختيارية بالكامل).
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'gender' => ['nullable', 'in:0,1'],
            'joining_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            // بيانات الولي.
            'guardian_relation' => ['nullable', 'string', 'max:190'],
            'guardian_phone' => ['nullable', 'string', 'max:40'],
            'guardian_wilaya' => ['nullable', 'string', 'max:190'],
            'guardian_dayra' => ['nullable', 'string', 'max:190'],
            'guardian_baladia' => ['nullable', 'string', 'max:190'],
            // بيانات التلميذ.
            'guardian_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'student_phone' => ['nullable', 'string', 'max:40'],
            'student_birth_date' => ['nullable', 'date'],
            'student_birth_place' => ['nullable', 'string', 'max:190'],
            'student_wilaya' => ['nullable', 'string', 'max:190'],
            'student_dayra' => ['nullable', 'string', 'max:190'],
            'student_baladia' => ['nullable', 'string', 'max:190'],
        ]);

        $role = $validated['role'] ?? null;
        $this->validateRoleProfile($role, $validated);

        $fullName = $validated['name'];
        $nameArr = ['ar' => $fullName, 'fr' => $fullName, 'en' => $fullName];

        $user = $createPortalUser->execute([
            'name' => $nameArr,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
            'school_id' => $this->currentSchoolId(),
            'must_change_password' => true,
            'profile' => $this->buildProfilePayload($role, $validated, $nameArr),
        ]);

        MenuAccessService::bustCache();

        return response()->json([
            'ok' => true,
            'message' => trans('roles.user_created'),
            'user' => [
                'id' => $user->id,
                'name' => $fullName,
                'email' => $user->email,
                'roles' => $role ? [$role] : [],
            ],
        ]);
    }

    /** التلميذ يحتاج وليّاً وقسماً؛ نتحقّق منهما فقط عند اختيار دور تلميذ. */
    private function validateRoleProfile(?string $role, array $data): void
    {
        if ($role !== 'student') {
            return;
        }

        $errors = [];
        if (empty($data['guardian_user_id'])) {
            $errors['guardian_user_id'] = trans('roles.student_needs_guardian');
        }
        if (empty($data['section_id'])) {
            $errors['section_id'] = trans('roles.student_needs_section');
        }
        // بلا تاريخ ميلاد كان يُحفظ تاريخ اليوم كتاريخ ميلاد وهمي.
        if (empty($data['student_birth_date'])) {
            $errors['student_birth_date'] = trans('roles.student_needs_birth_date');
        }

        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /** يبني حمولة الملف المرتبط بالدور للتمريرها إلى إجراء الإنشاء الموحّد. */
    private function buildProfilePayload(?string $role, array $data, array $nameArr): array
    {
        if ($role === 'teacher') {
            return [
                'specialization_id' => $data['specialization_id'] ?? null,
                'gender' => $data['gender'] ?? null,
                'joining_date' => $data['joining_date'] ?? null,
                'address' => $data['address'] ?? null,
            ];
        }

        if ($role === 'guardian') {
            return [
                'prenom' => $nameArr,
                'nom' => ['ar' => '', 'fr' => '', 'en' => ''],
                'relation' => $data['guardian_relation'] ?? '',
                'phone' => $data['guardian_phone'] ?? 0,
                'address' => $data['address'] ?? '',
                'wilaya' => $data['guardian_wilaya'] ?? '',
                'dayra' => $data['guardian_dayra'] ?? '',
                'baladia' => $data['guardian_baladia'] ?? '',
            ];
        }

        if ($role === 'student') {
            return [
                'prenom' => $nameArr,
                'nom' => ['ar' => '', 'fr' => '', 'en' => ''],
                'guardian_user_id' => $data['guardian_user_id'] ?? null,
                'section_id' => $data['section_id'] ?? null,
                'gender' => $data['gender'] ?? 0,
                'phone' => $data['student_phone'] ?? 0,
                'birth_date' => $data['student_birth_date'] ?? null,
                'birth_place' => $data['student_birth_place'] ?? '',
                'wilaya' => $data['student_wilaya'] ?? '',
                'dayra' => $data['student_dayra'] ?? '',
                'baladia' => $data['student_baladia'] ?? '',
            ];
        }

        return [];
    }

    public function updateUserRoles(Request $request, User $user, CreatePortalUserAction $createPortalUser)
    {
        $this->assertSameSchool($user);

        // منع المسؤول من تغيير دوره بنفسه حتى لا يفقد صلاحية الإدارة.
        if ((int) $user->id === (int) Auth::id()) {
            return response()->json(['ok' => false, 'message' => trans('roles.cannot_change_own_role')], 422);
        }

        if ($request->input('role') === '') {
            $request->merge(['role' => null]);
        }

        $validated = $request->validate([
            // دور واحد فقط لكل مستخدم.
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $role = $validated['role'] ?? null;
        DB::transaction(function () use ($user, $role, $createPortalUser) {
            $createPortalUser->ensureProfileForRole($user, $role);
            $user->syncRoles($role ? [$role] : []);
        });
        MenuAccessService::bustCache();

        return response()->json(['ok' => true, 'message' => trans('roles.roles_updated')]);
    }

    public function resetUserPassword(Request $request, User $user)
    {
        $this->assertSameSchool($user);

        $validated = $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'max:100'],
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
