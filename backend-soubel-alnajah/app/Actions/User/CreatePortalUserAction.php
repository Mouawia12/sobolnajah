<?php

namespace App\Actions\User;

use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\Inscription\Teacher;
use App\Models\School\Section;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * مصدر موحّد لإنشاء مستخدم بوابة مع إسناد دور واحد وبناء ملفه حسب الدور.
 *
 * - إسناد دور واحد فقط لكل مستخدم (syncRoles).
 * - إن كان الدور من أدوار النظام التي تحتاج ملفاً (teacher/guardian/student)
 *   يُنشأ الملف المرتبط حتى لا تكون بيانات المستخدم ناقصة عند الدخول.
 * - بيانات المعلّم اختيارية: يمكن إنشاؤه بحساب فقط وإسناد التخصص/المواد لاحقاً.
 */
class CreatePortalUserAction
{
    /**
     * @param array{
     *   name: array<string,string>,
     *   email: string,
     *   password: string,
     *   role?: string|null,
     *   school_id?: int|null,
     *   must_change_password?: bool,
     *   profile?: array<string,mixed>
     * } $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'must_change_password' => $data['must_change_password'] ?? true,
                'school_id' => $data['school_id'] ?? null,
            ]);

            $role = $data['role'] ?? null;
            // دور واحد فقط لكل مستخدم.
            $user->syncRoles($role ? [$role] : []);

            if ($role) {
                $this->buildProfile($user, $role, $data);
            }

            return $user;
        });
    }

    private function buildProfile(User $user, string $role, array $data): void
    {
        $profile = $data['profile'] ?? [];
        $fullName = $this->pickName($data['name']);

        if ($role === 'teacher') {
            Teacher::create([
                'user_id' => $user->id,
                'specialization_id' => $profile['specialization_id'] ?? null,
                'name' => $fullName,
                'gender' => isset($profile['gender']) && $profile['gender'] !== '' ? (int) $profile['gender'] : null,
                'joining_date' => $profile['joining_date'] ?? null,
                'address' => $profile['address'] ?? null,
            ]);

            return;
        }

        if ($role === 'guardian') {
            MyParent::create([
                'user_id' => $user->id,
                'prenomwali' => $profile['prenom'] ?? ['ar' => $fullName, 'fr' => $fullName, 'en' => $fullName],
                'nomwali' => $profile['nom'] ?? ['ar' => '', 'fr' => '', 'en' => ''],
                'relationetudiant' => $profile['relation'] ?? '',
                'adressewali' => $profile['address'] ?? '',
                'wilayawali' => $profile['wilaya'] ?? '',
                'dayrawali' => $profile['dayra'] ?? '',
                'baladiawali' => $profile['baladia'] ?? '',
                'numtelephonewali' => $profile['phone'] ?? 0,
            ]);

            return;
        }

        if ($role === 'student') {
            [$sectionId, $parentId] = $this->resolveStudentRelations($profile, $data['school_id'] ?? null);

            StudentInfo::create([
                'user_id' => $user->id,
                'section_id' => $sectionId,
                'parent_id' => $parentId,
                'gender' => isset($profile['gender']) && $profile['gender'] !== '' ? (int) $profile['gender'] : 0,
                'prenom' => $profile['prenom'] ?? ['ar' => $fullName, 'fr' => $fullName, 'en' => $fullName],
                'nom' => $profile['nom'] ?? ['ar' => '', 'fr' => '', 'en' => ''],
                'datenaissance' => $profile['birth_date'] ?? now()->toDateString(),
                'lieunaissance' => $profile['birth_place'] ?? '',
                'wilaya' => $profile['wilaya'] ?? '',
                'dayra' => $profile['dayra'] ?? '',
                'baladia' => $profile['baladia'] ?? '',
                'numtelephone' => $profile['phone'] ?? 0,
                'born_by_judgment' => 0,
            ]);
        }
    }

    /**
     * يضمن وجود الملف المرتبط بالدور عند تغيير دور مستخدم موجود،
     * حتى لا يدخل أستاذ/ولي بلا ملف (نفس خطأ الدخول الذي أصلحه الإنشاء).
     * التلميذ يحتاج قسماً ووليّاً، فلا يمكن بناء ملفه من تغيير الدور وحده.
     */
    public function ensureProfileForRole(User $user, ?string $role): void
    {
        if ($role === 'teacher' && !Teacher::query()->where('user_id', $user->id)->exists()) {
            $this->buildProfile($user, 'teacher', ['name' => $this->nameArray($user)]);
        } elseif ($role === 'guardian' && !$user->parentProfile()->exists()) {
            $this->buildProfile($user, 'guardian', ['name' => $this->nameArray($user)]);
        } elseif ($role === 'student' && !$user->studentProfile()->exists()) {
            throw ValidationException::withMessages([
                'role' => trans('roles.student_role_needs_profile'),
            ]);
        }
    }

    /**
     * يتحقّق أن قسم التلميذ ووليّه صالحان وضمن نفس المؤسسة.
     *
     * @return array{0:int,1:int} [section_id, parent_id]
     */
    private function resolveStudentRelations(array $profile, ?int $schoolId): array
    {
        $section = Section::query()->find((int) ($profile['section_id'] ?? 0));
        if (!$section || ($schoolId && (int) $section->school_id !== (int) $schoolId)) {
            throw ValidationException::withMessages([
                'section_id' => trans('roles.invalid_section'),
            ]);
        }

        $guardianUser = User::query()
            ->whereKey((int) ($profile['guardian_user_id'] ?? 0))
            ->with('parentProfile')
            ->first();

        if (!$guardianUser || !$guardianUser->hasRole('guardian') || !$guardianUser->parentProfile
            || ($schoolId && (int) $guardianUser->school_id !== (int) $schoolId)) {
            throw ValidationException::withMessages([
                'guardian_user_id' => trans('roles.invalid_guardian'),
            ]);
        }

        return [(int) $section->id, (int) $guardianUser->parentProfile->id];
    }

    private function nameArray(User $user): array
    {
        $name = $user->getTranslations('name');

        return $name ?: ['ar' => (string) $user->name];
    }

    /** يختار اسماً نصياً مناسباً من مصفوفة الأسماء المترجمة. */
    private function pickName(array $name): string
    {
        return (string) ($name['ar'] ?? $name['fr'] ?? $name['en'] ?? reset($name) ?? '');
    }
}
