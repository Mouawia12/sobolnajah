<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\MyParent;
use App\Models\Inscription\StudentInfo;
use App\Models\School\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UpdateStudentEnrollmentAction
{
    public function execute(StudentInfo $student, array $input, Section $section): void
    {
        DB::transaction(function () use ($student, $input, $section) {
            $student->update([
                'section_id' => $section->id,
                'prenom' => [
                    'fr' => $input['prenomfr'] ?? null,
                    'ar' => $input['prenomar'] ?? null,
                    'en' => $input['prenomfr'] ?? null,
                ],
                'nom' => [
                    'fr' => $input['nomfr'] ?? null,
                    'ar' => $input['nomar'] ?? null,
                    'en' => $input['nomfr'] ?? null,
                ],
                'gender' => isset($input['gender']) ? (int) $input['gender'] : null,
                'numtelephone' => $input['numtelephone'] ?? null,
                'datenaissance' => $input['datenaissance'] ?? null,
                'lieunaissance' => $input['lieunaissance'] ?? null,
                'wilaya' => $input['wilaya'] ?? null,
                'dayra' => $input['dayra'] ?? null,
                'baladia' => $input['baladia'] ?? null,
            ]);

            if ($student->user) {
                $userData = [
                    'name' => [
                        'fr' => $input['prenomfr'] ?? null,
                        'ar' => $input['prenomar'] ?? null,
                        'en' => $input['prenomfr'] ?? null,
                    ],
                    'email' => $input['email'] ?? null,
                    'school_id' => $section->school_id,
                ];

                if (!empty($input['password'])) {
                    $userData['password'] = Hash::make($input['password']);
                    $userData['must_change_password'] = false;
                }

                $student->user->update($userData);
            }

            if ($student->parent) {
                $guardianFields = [
                    'prenomfrwali',
                    'prenomarwali',
                    'nomfrwali',
                    'nomarwali',
                    'relationetudiant',
                    'adressewali',
                    'wilayawali',
                    'dayrawali',
                    'baladiawali',
                    'numtelephonewali',
                    'emailwali',
                ];

                $hasGuardianInput = false;
                foreach ($guardianFields as $field) {
                    if (array_key_exists($field, $input) && trim((string) $input[$field]) !== '') {
                        $hasGuardianInput = true;
                        break;
                    }
                }

                if ($hasGuardianInput) {
                    $currentGuardian = $student->parent;
                    $resolvedGuardian = $this->resolveGuardianForUpdate($currentGuardian, $section->school_id, $input);

                    if ($resolvedGuardian && $currentGuardian && $resolvedGuardian->id !== $currentGuardian->id) {
                        // Re-link student to an existing guardian to prevent duplicates.
                        $student->update(['parent_id' => $resolvedGuardian->id]);
                    }

                    $guardian = $resolvedGuardian ?? $currentGuardian;
                    if (!$guardian) {
                        return;
                    }

                    $guardian->update([
                        'prenomwali' => [
                            'fr' => $input['prenomfrwali'] ?? $this->safeTranslation($guardian, 'prenomwali', 'fr'),
                            'ar' => $input['prenomarwali'] ?? $this->safeTranslation($guardian, 'prenomwali', 'ar'),
                            'en' => $input['prenomfrwali'] ?? $this->safeTranslation($guardian, 'prenomwali', 'en'),
                        ],
                        'nomwali' => [
                            'fr' => $input['nomfrwali'] ?? $this->safeTranslation($guardian, 'nomwali', 'fr'),
                            'ar' => $input['nomarwali'] ?? $this->safeTranslation($guardian, 'nomwali', 'ar'),
                            'en' => $input['nomfrwali'] ?? $this->safeTranslation($guardian, 'nomwali', 'en'),
                        ],
                        'relationetudiant' => $input['relationetudiant'] ?? $guardian->relationetudiant,
                        'adressewali' => $input['adressewali'] ?? $guardian->adressewali,
                        'wilayawali' => $input['wilayawali'] ?? $guardian->wilayawali,
                        'dayrawali' => $input['dayrawali'] ?? $guardian->dayrawali,
                        'baladiawali' => $input['baladiawali'] ?? $guardian->baladiawali,
                        'numtelephonewali' => $input['numtelephonewali'] ?? $guardian->numtelephonewali,
                    ]);

                    if ($guardian->user) {
                        $guardian->user->update([
                            'name' => [
                                'fr' => $input['prenomfrwali'] ?? $this->safeTranslation($guardian->user, 'name', 'fr'),
                                'ar' => $input['prenomarwali'] ?? $this->safeTranslation($guardian->user, 'name', 'ar'),
                                'en' => $input['prenomfrwali'] ?? $this->safeTranslation($guardian->user, 'name', 'en'),
                            ],
                            'email' => $input['emailwali'] ?? $guardian->user->email,
                            'school_id' => $section->school_id,
                        ]);
                    }
                }
            }
        });
    }

    private function resolveGuardianForUpdate(?MyParent $currentGuardian, int $schoolId, array $input): ?MyParent
    {
        if (!$currentGuardian) {
            return null;
        }

        $guardianPhone = $this->extractNonEmpty($input, 'numtelephonewali');
        $guardianEmail = $this->extractNonEmpty($input, 'emailwali');

        if (!$guardianPhone && !$guardianEmail) {
            return $currentGuardian;
        }

        $existingGuardian = MyParent::query()
            ->forSchool($schoolId)
            ->where('id', '!=', $currentGuardian->id)
            ->where(function (Builder $query) use ($guardianPhone, $guardianEmail) {
                if ($guardianPhone) {
                    $query->orWhere('numtelephonewali', $guardianPhone);
                }

                if ($guardianEmail) {
                    $query->orWhereHas('user', function (Builder $userQuery) use ($guardianEmail) {
                        $userQuery->where('email', $guardianEmail);
                    });
                }
            })
            ->first();

        return $existingGuardian ?: $currentGuardian;
    }

    private function safeTranslation($model, string $field, string $locale): ?string
    {
        try {
            $value = $model->getTranslation($field, $locale);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        } catch (Throwable) {
        }

        $raw = $model->{$field} ?? null;
        if (is_string($raw) && $raw !== '') {
            return $raw;
        }

        if (is_array($raw)) {
            return $raw[$locale] ?? reset($raw) ?: null;
        }

        return null;
    }

    private function extractNonEmpty(array $input, string $key): ?string
    {
        if (!array_key_exists($key, $input)) {
            return null;
        }

        $value = trim((string) $input[$key]);
        return $value === '' ? null : $value;
    }
}
