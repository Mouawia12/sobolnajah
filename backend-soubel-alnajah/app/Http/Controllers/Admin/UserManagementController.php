<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inscription\BuildLocalizedNameAction;
use App\Actions\User\CreatePortalUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortalUserRequest;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\Teacher;
use App\Models\School\School;
use App\Models\School\Section;
use App\Models\Specialization\Specialization;
use App\Models\User;
use App\Support\RoleCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function __construct(private BuildLocalizedNameAction $buildLocalizedNameAction)
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function create(Request $request)
    {
        $currentSchoolId = $this->currentSchoolId();
        $filterQuery = trim((string) $request->query('filter_q', ''));
        $filterRole = trim((string) $request->query('filter_role', ''));
        $filterSchoolId = $request->query('filter_school_id');

        // قائمة أدوار موحّدة مع صفحة «الأدوار والصلاحيات» وصندوق الحسابات.
        $roles = RoleCatalog::CORE_ROLES;

        $schools = School::query()
            ->when($currentSchoolId, fn ($query) => $query->whereKey($currentSchoolId))
            ->select(['id', 'name_school'])
            ->orderBy('id')
            ->get();

        $specializations = Specialization::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $sections = Section::query()
            ->when($currentSchoolId, fn ($query) => $query->where('school_id', $currentSchoolId))
            ->with([
                'classroom:id,grade_id,name_class',
                'classroom.schoolgrade:id,name_grade',
            ])
            ->select(['id', 'school_id', 'classroom_id', 'name_section'])
            ->orderBy('id')
            ->get();

        $guardians = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'guardian'))
            ->with(['parentProfile', 'school'])
            ->when($currentSchoolId, fn ($query) => $query->where('school_id', $currentSchoolId))
            ->select(['id', 'name', 'email', 'school_id'])
            ->orderByDesc('id')
            ->get()
            ->filter(fn (User $guardian) => $guardian->parentProfile);

        $users = User::query()
            ->when($currentSchoolId, fn ($query) => $query->where('school_id', $currentSchoolId))
            ->with([
                'roles:id,name,display_name',
                'school:id,name_school',
            ])
            ->select(['id', 'name', 'email', 'school_id', 'created_at'])
            ->when($filterQuery !== '', function ($query) use ($filterQuery) {
                $query->where(function ($nested) use ($filterQuery) {
                    $nested->where('email', 'like', '%' . $filterQuery . '%')
                        ->orWhere('name->fr', 'like', '%' . $filterQuery . '%')
                        ->orWhere('name->ar', 'like', '%' . $filterQuery . '%')
                        ->orWhere('name->en', 'like', '%' . $filterQuery . '%');

                    if (ctype_digit($filterQuery)) {
                        $nested->orWhere('id', (int) $filterQuery);
                    }
                });
            })
            ->when($filterRole !== '', function ($query) use ($filterRole) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', $filterRole));
            })
            ->when(!$currentSchoolId && $filterSchoolId, function ($query) use ($filterSchoolId) {
                $query->where('school_id', (int) $filterSchoolId);
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.users_table', [
                    'users' => $users,
                ])->render(),
            ]);
        }

        return view('admin.users.create', [
            'roles' => $roles,
            'schools' => $schools,
            'sections' => $sections,
            'specializations' => $specializations,
            'guardians' => $guardians,
            'users' => $users,
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'إضافة مستخدم'],
            ],
        ]);
    }

    public function store(StorePortalUserRequest $request, CreatePortalUserAction $createPortalUser): RedirectResponse
    {
        $validated = $request->validated();
        $role = (string) $validated['role'];

        $targetSchoolId = $this->resolveTargetSchoolId($validated['school_id'] ?? null);
        $this->guardSchoolAccess($targetSchoolId);

        $name = [
            'fr' => $this->joinNameParts($validated['first_name_fr'] ?? null, $validated['last_name_fr'] ?? null),
            'ar' => $this->joinNameParts($validated['first_name_ar'] ?? null, $validated['last_name_ar'] ?? null),
            'en' => $this->joinNameParts($validated['first_name_fr'] ?? null, $validated['last_name_fr'] ?? null)
                ?: $this->joinNameParts($validated['first_name_ar'] ?? null, $validated['last_name_ar'] ?? null),
        ];

        // تحقّق قسم/ولي التلميذ (نفس المؤسسة) يتمّ داخل إجراء الإنشاء الموحّد.
        $createPortalUser->execute([
            'name' => $name,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
            'school_id' => $targetSchoolId,
            'must_change_password' => false,
            'profile' => $this->buildProfilePayload($role, $validated),
        ]);

        return redirect()->route('admin.users.create')->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    /** يبني حمولة الملف حسب الدور لتمريرها إلى إجراء الإنشاء الموحّد. */
    private function buildProfilePayload(string $role, array $v): array
    {
        if ($role === 'teacher') {
            return [
                'specialization_id' => $v['specialization_id'] ?? null,
                'gender' => $v['gender'] ?? null,
                'joining_date' => $v['joining_date'] ?? null,
                'address' => $v['address'] ?? null,
            ];
        }

        if ($role === 'guardian') {
            return [
                'prenom' => $this->buildLocalizedNameAction->execute($v['first_name_fr'] ?? null, $v['first_name_ar'] ?? null),
                'nom' => $this->buildLocalizedNameAction->execute($v['last_name_fr'] ?? null, $v['last_name_ar'] ?? null),
                'relation' => $v['guardian_relation'] ?? '',
                'phone' => $v['guardian_phone'] ?? 0,
                'address' => $v['address'] ?? '',
                'wilaya' => $v['guardian_wilaya'] ?? '',
                'dayra' => $v['guardian_dayra'] ?? '',
                'baladia' => $v['guardian_baladia'] ?? '',
            ];
        }

        if ($role === 'student') {
            return [
                'prenom' => $this->buildLocalizedNameAction->execute($v['first_name_fr'] ?? null, $v['first_name_ar'] ?? null),
                'nom' => $this->buildLocalizedNameAction->execute($v['last_name_fr'] ?? null, $v['last_name_ar'] ?? null),
                'guardian_user_id' => $v['guardian_user_id'] ?? null,
                'section_id' => $v['section_id'] ?? null,
                'gender' => $v['gender'] ?? 0,
                'phone' => $v['student_phone'] ?? 0,
                'birth_date' => $v['student_birth_date'] ?? null,
                'birth_place' => $v['student_birth_place'] ?? '',
                'wilaya' => $v['student_wilaya'] ?? '',
                'dayra' => $v['student_dayra'] ?? '',
                'baladia' => $v['student_baladia'] ?? '',
            ];
        }

        return [];
    }

    private function resolveTargetSchoolId(mixed $requestedSchoolId): ?int
    {
        $adminSchoolId = Auth::user()?->school_id;

        if ($adminSchoolId) {
            return (int) $adminSchoolId;
        }

        return $requestedSchoolId ? (int) $requestedSchoolId : null;
    }

    private function guardSchoolAccess(?int $targetSchoolId): void
    {
        $adminSchoolId = Auth::user()?->school_id;

        if ($adminSchoolId && $targetSchoolId && (int) $adminSchoolId !== (int) $targetSchoolId) {
            abort(403);
        }
    }

    private function joinNameParts(?string $first, ?string $last): ?string
    {
        $first = trim((string) $first);
        $last = trim((string) $last);

        $full = trim($first . ' ' . $last);

        return $full !== '' ? $full : null;
    }
}
