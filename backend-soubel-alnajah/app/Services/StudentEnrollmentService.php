<?php

namespace App\Services;

use App\Actions\Inscription\ProvisionSchoolUserAction;
use App\Actions\Inscription\BuildLocalizedNameAction;
use App\Actions\Inscription\UpdateGuardianAccountAction;
use App\Actions\Inscription\BuildGuardianProfilePayloadAction;
use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StudentEnrollmentService
{
    public function __construct(
        private ProvisionSchoolUserAction $provisionSchoolUserAction,
        private BuildLocalizedNameAction $buildLocalizedNameAction,
        private UpdateGuardianAccountAction $updateGuardianAccountAction,
        private BuildGuardianProfilePayloadAction $buildGuardianProfilePayloadAction
    )
    {
    }

    public function createStudent(array $studentData, array $guardianData, Section $section): StudentInfo
    {
        $schoolId = $section->school_id;

        $this->assertStudentIsUnique($studentData, $guardianData, $section);

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

    public function importStudent(array $studentData, array $guardianData, Section $section): array
    {
        $dateOfBirth = $this->normalizeDate(Arr::get($studentData, 'birth_date'));
        $studentPhone = $this->normalizeDigits(Arr::get($studentData, 'phone'));
        $guardianPhone = $this->normalizeDigits(Arr::get($guardianData, 'phone'));

        $matchedStudent = $this->findMatchingStudent(
            $studentData,
            $section->school_id,
            $dateOfBirth,
            $studentPhone,
            $guardianPhone
        );

        if ($matchedStudent) {
            if ((int) $matchedStudent->section_id !== (int) $section->id) {
                $matchedStudent->update(['section_id' => $section->id]);

                return [
                    'status' => 'section_updated',
                    'student' => $matchedStudent->fresh(['user', 'parent', 'section']),
                ];
            }

            return [
                'status' => 'duplicate',
                'student' => $matchedStudent,
            ];
        }

        return [
            'status' => 'created',
            'student' => $this->createStudent($studentData, $guardianData, $section),
        ];
    }

    protected function assertStudentIsUnique(array $studentData, array $guardianData, Section $section): void
    {
        $firstName = Arr::get($studentData, 'first_name.fr');
        $lastName = Arr::get($studentData, 'last_name.fr');
        $dateOfBirth = $this->normalizeDate(Arr::get($studentData, 'birth_date'));
        $studentPhone = $this->normalizeDigits(Arr::get($studentData, 'phone'));
        $guardianPhone = $this->normalizeDigits(Arr::get($guardianData, 'phone'));
        $email = Str::lower(trim((string) Arr::get($studentData, 'email')));

        if (!$firstName || !$lastName) {
            throw ValidationException::withMessages([
                'student' => 'Student first and last name are required.',
            ]);
        }

        if ($email && User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages([
                'student_email' => 'A user with the provided student email already exists.',
            ]);
        }

        if ($this->findMatchingStudent(
            $studentData,
            $section->school_id,
            $dateOfBirth,
            $studentPhone,
            $guardianPhone
        )) {
            throw ValidationException::withMessages([
                'student' => 'A student with similar identity data already exists for this school.',
            ]);
        }
    }

    protected function findMatchingStudent(
        array $studentData,
        int $schoolId,
        ?string $dateOfBirth,
        ?string $studentPhone,
        ?string $guardianPhone
    ): ?StudentInfo {
        $firstNames = Arr::get($studentData, 'first_name', []);
        $lastNames = Arr::get($studentData, 'last_name', []);
        $sourceFingerprints = $this->buildNameFingerprints($firstNames, $lastNames);
        $sourceNameTokens = $this->extractNameTokens($firstNames, $lastNames);

        $duplicateQuery = StudentInfo::query()
            ->forSchool($schoolId)
            ->with(['parent:id,numtelephonewali', 'user:id,email'])
            ->select(['id', 'user_id', 'parent_id', 'section_id', 'prenom', 'nom', 'datenaissance', 'numtelephone']);

        $hasSignalFilter = false;

        $duplicateQuery->where(function (Builder $query) use (
            $dateOfBirth,
            $studentPhone,
            $guardianPhone,
            $firstNames,
            $lastNames,
            &$hasSignalFilter
        ) {
            if ($dateOfBirth) {
                $query->orWhereDate('datenaissance', $dateOfBirth);
                $hasSignalFilter = true;
            }

            if ($studentPhone) {
                $query->orWhere('numtelephone', $studentPhone);
                $hasSignalFilter = true;
            }

            if ($guardianPhone) {
                $query->orWhereHas('parent', function (Builder $parentQuery) use ($guardianPhone) {
                    $parentQuery->where('numtelephonewali', $guardianPhone);
                });
                $hasSignalFilter = true;
            }

            foreach (['fr', 'ar', 'en'] as $lang) {
                $firstName = trim((string) Arr::get($firstNames, $lang));
                if ($firstName !== '') {
                    $query->orWhere("prenom->{$lang}", $firstName);
                    $hasSignalFilter = true;
                }

                $lastName = trim((string) Arr::get($lastNames, $lang));
                if ($lastName !== '') {
                    $query->orWhere("nom->{$lang}", $lastName);
                    $hasSignalFilter = true;
                }
            }
        });

        if (!$hasSignalFilter) {
            return null;
        }

        $candidates = $duplicateQuery->limit(250)->get();

        foreach ($candidates as $candidate) {
            $candidateFirstNames = method_exists($candidate, 'getTranslations')
                ? $candidate->getTranslations('prenom')
                : ['fr' => $candidate->prenom];
            $candidateLastNames = method_exists($candidate, 'getTranslations')
                ? $candidate->getTranslations('nom')
                : ['fr' => $candidate->nom];

            $candidateFingerprints = $this->buildNameFingerprints($candidateFirstNames, $candidateLastNames);
            $candidateNameTokens = $this->extractNameTokens($candidateFirstNames, $candidateLastNames);

            $sameFullName = !empty(array_intersect($sourceFingerprints, $candidateFingerprints));
            $nameSimilarity = $sameFullName || $this->nameTokensMatch($sourceNameTokens, $candidateNameTokens);

            $candidateBirthDate = $this->normalizeDate((string) $candidate->datenaissance);
            $sameBirthDate = $dateOfBirth && $candidateBirthDate && $dateOfBirth === $candidateBirthDate;
            $sameStudentPhone = $studentPhone
                && $studentPhone === $this->normalizeDigits((string) $candidate->numtelephone);
            $sameGuardianPhone = $guardianPhone
                && $guardianPhone === $this->normalizeDigits((string) optional($candidate->parent)->numtelephonewali);

            if (($nameSimilarity && $sameBirthDate)
                || ($nameSimilarity && ($sameStudentPhone || $sameGuardianPhone))
                || ($sameBirthDate && $sameStudentPhone)
                || ($sameStudentPhone && $sameGuardianPhone && $sameBirthDate)
            ) {
                return $candidate;
            }
        }

        return null;
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
            $this->updateGuardianAccountAction->execute($guardian, $guardianData, $schoolId);

            return tap($guardian)->update(
                $this->buildGuardianProfilePayloadAction->execute($guardianData, $phone)
            );
        }

        $guardianUser = $this->createUser(
            $this->normalizeName($guardianData['first_name'] ?? []),
            $email,
            $schoolId,
            'guardian'
        );

        return MyParent::create(
            $this->buildGuardianProfilePayloadAction->execute($guardianData, $phone) + [
                'user_id' => $guardianUser->id,
            ]
        );
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

        return $this->provisionSchoolUserAction->execute($name, $email, $schoolId, $role);
    }

    protected function normalizeName(array $name): array
    {
        return $this->buildLocalizedNameAction->execute(
            Arr::get($name, 'fr'),
            Arr::get($name, 'ar'),
            Arr::get($name, 'en'),
            true
        );
    }

    protected function buildNameFingerprints(array $firstNames, array $lastNames): array
    {
        $fingerprints = [];

        foreach (['fr', 'ar', 'en'] as $lang) {
            $fullName = trim((string) Arr::get($firstNames, $lang) . ' ' . (string) Arr::get($lastNames, $lang));
            $normalized = $this->normalizePersonName($fullName);
            if ($normalized !== '') {
                $fingerprints[] = $normalized;
            }
        }

        return array_values(array_unique($fingerprints));
    }

    protected function extractNameTokens(array $firstNames, array $lastNames): array
    {
        $tokens = [];

        foreach (['fr', 'ar', 'en'] as $lang) {
            $first = $this->normalizePersonName((string) Arr::get($firstNames, $lang));
            $last = $this->normalizePersonName((string) Arr::get($lastNames, $lang));
            if ($first !== '' && $last !== '') {
                $tokens[] = $first . ':' . $last;
            }
        }

        return array_values(array_unique($tokens));
    }

    protected function nameTokensMatch(array $sourceTokens, array $candidateTokens): bool
    {
        if (empty($sourceTokens) || empty($candidateTokens)) {
            return false;
        }

        foreach ($sourceTokens as $sourceToken) {
            [$sourceFirst, $sourceLast] = array_pad(explode(':', $sourceToken, 2), 2, '');

            foreach ($candidateTokens as $candidateToken) {
                [$candidateFirst, $candidateLast] = array_pad(explode(':', $candidateToken, 2), 2, '');

                if ($sourceFirst === $candidateFirst && $sourceLast === $candidateLast) {
                    return true;
                }

                if ($this->textLooksSimilar($sourceFirst, $candidateFirst)
                    && $this->textLooksSimilar($sourceLast, $candidateLast)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function textLooksSimilar(string $a, string $b): bool
    {
        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b) {
            return true;
        }

        if (strlen($a) < 4 || strlen($b) < 4) {
            return false;
        }

        return levenshtein($a, $b) <= 1;
    }

    protected function normalizeDigits(?string $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    protected function normalizeDate(?string $value): ?string
    {
        $date = trim((string) $value);
        if ($date === '') {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizePersonName(string $value): string
    {
        $normalized = Str::lower(trim($value));
        if ($normalized === '') {
            return '';
        }

        // Remove Arabic diacritics/tatweel and punctuation to reduce spelling variance.
        $normalized = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $normalized);
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized);

        return trim((string) $normalized);
    }
}
