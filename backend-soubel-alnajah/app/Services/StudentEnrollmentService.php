<?php

namespace App\Services;

use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class StudentEnrollmentService
{
    public function __construct(private UserOnboardingService $onboardingService)
    {
    }

    public function createStudent(array $studentData, array $guardianData, Section $section): StudentInfo
    {
        $schoolId = $section->school_id;

        $this->assertStudentIsUnique($studentData, $section);

        return DB::transaction(function () use ($studentData, $guardianData, $section, $schoolId) {
            $guardian = $this->resolveGuardian($guardianData, $schoolId);
            $studentUser = $this->createStudentUser($studentData, $schoolId);

            $studentInfo = StudentInfo::create(array_filter([
                'user_id' => $studentUser->id,
                'section_id' => $section->id,
                'parent_id' => $guardian->id,
                'prenom' => $this->normalizeName($studentData['first_name'] ?? []),
                'nom' => $this->normalizeName($studentData['last_name'] ?? []),
                'gender' => $studentData['gender'] ?? null,
                'numtelephone' => $studentData['phone'] ?? null,
                'datenaissance' => $studentData['birth_date'] ?? null,
                'lieunaissance' => $studentData['birth_place'] ?? null,
                'wilaya' => $studentData['wilaya'] ?? null,
                'dayra' => $studentData['dayra'] ?? null,
                'baladia' => $studentData['baladia'] ?? null,
            ], fn ($value) => !is_null($value)));

            return $studentInfo->fresh(['user', 'parent', 'section']);
        });
    }

    protected function assertStudentIsUnique(array $studentData, Section $section): void
    {
        $firstName = Arr::get($studentData, 'first_name.fr');
        $lastName = Arr::get($studentData, 'last_name.fr');
        $dateOfBirth = Arr::get($studentData, 'birth_date');
        $email = Arr::get($studentData, 'email');

        if (!$firstName || !$lastName) {
            throw ValidationException::withMessages([
                'student' => 'Student first and last name are required.',
            ]);
        }

        $duplicateQuery = StudentInfo::query()
            ->forSchool($section->school_id)
            ->where('prenom->fr', $firstName)
            ->where('nom->fr', $lastName);

        if ($dateOfBirth) {
            $duplicateQuery->whereDate('datenaissance', $dateOfBirth);
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'student' => 'A student with the same name already exists for this school.',
            ]);
        }

        if ($email && User::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'student_email' => 'A user with the provided student email already exists.',
            ]);
        }
    }

    protected function resolveGuardian(array $guardianData, int $schoolId): MyParent
    {
        $phone = Arr::get($guardianData, 'phone');
        $email = Arr::get($guardianData, 'email');

        if (!$phone && !$email) {
            throw ValidationException::withMessages([
                'guardian' => 'Guardian email or phone is required.',
            ]);
        }

        $guardian = MyParent::query()
            ->where(function (Builder $query) use ($phone, $email) {
                if ($phone) {
                    $query->orWhere('numtelephonewali', $phone);
                }

                if ($email) {
                    $query->orWhereHas('user', function (Builder $userQuery) use ($email) {
                        $userQuery->where('email', $email);
                    });
                }
            })
            ->first();

        if ($guardian) {
            $this->syncGuardianAccount($guardian, $guardianData, $schoolId);

            return tap($guardian)->update(array_filter([
                'prenomwali' => $this->normalizeName($guardianData['first_name'] ?? []),
                'nomwali' => $this->normalizeName($guardianData['last_name'] ?? []),
                'relationetudiant' => Arr::get($guardianData, 'relation'),
                'adressewali' => Arr::get($guardianData, 'address'),
                'wilayawali' => Arr::get($guardianData, 'wilaya'),
                'dayrawali' => Arr::get($guardianData, 'dayra'),
                'baladiawali' => Arr::get($guardianData, 'baladia'),
                'numtelephonewali' => $phone,
            ], fn ($value) => !is_null($value)));
        }

        $guardianUser = $this->createUser(
            $this->normalizeName($guardianData['first_name'] ?? []),
            $email,
            $schoolId,
            'guardian'
        );

        return MyParent::create([
            'prenomwali' => $this->normalizeName($guardianData['first_name'] ?? []),
            'nomwali' => $this->normalizeName($guardianData['last_name'] ?? []),
            'relationetudiant' => Arr::get($guardianData, 'relation'),
            'adressewali' => Arr::get($guardianData, 'address'),
            'wilayawali' => Arr::get($guardianData, 'wilaya'),
            'dayrawali' => Arr::get($guardianData, 'dayra'),
            'baladiawali' => Arr::get($guardianData, 'baladia'),
            'numtelephonewali' => $phone,
            'user_id' => $guardianUser->id,
        ]);
    }

    protected function createStudentUser(array $studentData, int $schoolId): User
    {
        $email = Arr::get($studentData, 'email');

        if (!$email) {
            throw ValidationException::withMessages([
                'student_email' => 'Student email is required.',
            ]);
        }

        $name = $this->normalizeName($studentData['first_name'] ?? []);

        return $this->createUser(
            $name,
            $email,
            $schoolId,
            'student'
        );
    }

    protected function createUser(array $name, ?string $email, int $schoolId, string $role): User
    {
        if (!$email) {
            throw ValidationException::withMessages([
                'user_email' => 'User email is required.',
            ]);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            // Create unpredictable temporary password instead of static defaults.
            'password' => Hash::make(Str::random(40)),
            'must_change_password' => true,
            'school_id' => $schoolId,
        ]);

        if (!$user->hasRole($role)) {
            $user->attachRole($role);
        }

        $this->onboardingService->dispatchPasswordSetupLink($user);

        return $user;
    }

    protected function syncGuardianAccount(MyParent $guardian, array $guardianData, int $schoolId): void
    {
        $user = $guardian->user;

        if (!$user) {
            throw new ModelNotFoundException('Guardian user account not found.');
        }

        $email = Arr::get($guardianData, 'email');

        $user->fill([
            'name' => $this->normalizeName($guardianData['first_name'] ?? []),
            'email' => $email ?: $user->email,
            'school_id' => $schoolId,
        ]);

        $user->save();

        if (!$user->hasRole('guardian')) {
            $user->attachRole('guardian');
        }
    }

    protected function normalizeName(array $name): array
    {
        $fr = Arr::get($name, 'fr');
        $ar = Arr::get($name, 'ar');
        $en = Arr::get($name, 'en') ?? $fr ?? $ar;

        return array_filter([
            'fr' => $fr,
            'ar' => $ar,
            'en' => $en,
        ], fn ($value) => !is_null($value));
    }
}
