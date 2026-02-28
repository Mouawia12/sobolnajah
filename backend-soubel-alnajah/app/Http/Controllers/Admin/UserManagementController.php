<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Inscription\BuildLocalizedNameAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortalUserRequest;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\Teacher;
use App\Models\School\School;
use App\Models\School\Section;
use App\Models\Specialization\Specialization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    public function __construct(private BuildLocalizedNameAction $buildLocalizedNameAction)
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function create()
    {
        $currentSchoolId = $this->currentSchoolId();

        $roles = [
            'admin' => 'مدير',
            'teacher' => 'معلم',
            'student' => 'تلميذ',
            'guardian' => 'ولي',
            'accountant' => 'محاسب',
        ];

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

        return view('admin.users.create', [
            'roles' => $roles,
            'schools' => $schools,
            'sections' => $sections,
            'specializations' => $specializations,
            'guardians' => $guardians,
            'notify' => $this->notifications(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'إضافة مستخدم'],
            ],
        ]);
    }

    public function store(StorePortalUserRequest $request): RedirectResponse
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

        DB::transaction(function () use ($validated, $role, $targetSchoolId, $name): void {
            $user = User::create([
                'name' => $name,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'must_change_password' => false,
                'school_id' => $targetSchoolId,
            ]);

            if (!$user->hasRole($role)) {
                $user->attachRole($role);
            }

            if ($role === 'teacher') {
                Teacher::create([
                    'user_id' => $user->id,
                    'specialization_id' => (int) $validated['specialization_id'],
                    'name' => $name,
                    'gender' => (int) $validated['gender'],
                    'joining_date' => $validated['joining_date'],
                    'address' => $validated['address'],
                ]);
            }

            if ($role === 'guardian') {
                MyParent::create([
                    'user_id' => $user->id,
                    'prenomwali' => $this->buildLocalizedNameAction->execute(
                        $validated['first_name_fr'] ?? null,
                        $validated['first_name_ar'] ?? null
                    ),
                    'nomwali' => $this->buildLocalizedNameAction->execute(
                        $validated['last_name_fr'] ?? null,
                        $validated['last_name_ar'] ?? null
                    ),
                    'relationetudiant' => $validated['guardian_relation'],
                    'adressewali' => $validated['address'],
                    'wilayawali' => $validated['guardian_wilaya'],
                    'dayrawali' => $validated['guardian_dayra'],
                    'baladiawali' => $validated['guardian_baladia'],
                    'numtelephonewali' => $validated['guardian_phone'],
                ]);
            }

            if ($role === 'student') {
                $section = Section::query()->findOrFail((int) $validated['section_id']);
                if ($targetSchoolId && (int) $section->school_id !== (int) $targetSchoolId) {
                    throw ValidationException::withMessages([
                        'section_id' => 'القسم المختار لا ينتمي لنفس المؤسسة.',
                    ]);
                }

                $guardianUser = User::query()
                    ->whereKey((int) $validated['guardian_user_id'])
                    ->with('parentProfile')
                    ->firstOrFail();

                if (!$guardianUser->hasRole('guardian') || !$guardianUser->parentProfile) {
                    throw ValidationException::withMessages([
                        'guardian_user_id' => 'ولي التلميذ غير صالح.',
                    ]);
                }

                if ($targetSchoolId && (int) $guardianUser->school_id !== (int) $targetSchoolId) {
                    throw ValidationException::withMessages([
                        'guardian_user_id' => 'ولي التلميذ يجب أن يكون من نفس المؤسسة.',
                    ]);
                }

                StudentInfo::create([
                    'user_id' => $user->id,
                    'section_id' => (int) $validated['section_id'],
                    'parent_id' => (int) $guardianUser->parentProfile->id,
                    'gender' => (int) $validated['gender'],
                    'prenom' => $this->buildLocalizedNameAction->execute(
                        $validated['first_name_fr'] ?? null,
                        $validated['first_name_ar'] ?? null
                    ),
                    'nom' => $this->buildLocalizedNameAction->execute(
                        $validated['last_name_fr'] ?? null,
                        $validated['last_name_ar'] ?? null
                    ),
                    'datenaissance' => $validated['student_birth_date'],
                    'lieunaissance' => $validated['student_birth_place'],
                    'wilaya' => $validated['student_wilaya'],
                    'dayra' => $validated['student_dayra'],
                    'baladia' => $validated['student_baladia'],
                    'numtelephone' => $validated['student_phone'],
                ]);
            }
        });

        return redirect()->route('admin.users.create')->with('success', 'تم إنشاء المستخدم بنجاح.');
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
