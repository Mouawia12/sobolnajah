<?php

namespace App\Actions\Inscription;

class BuildStudentEnrollmentPayloadAction
{
    /**
     * @return array{student: array<string, mixed>, guardian: array<string, mixed>}
     */
    public function execute(array $input): array
    {
        return [
            'student' => [
                'first_name' => [
                    'fr' => $input['prenomfr'] ?? null,
                    'ar' => $input['prenomar'] ?? null,
                    'en' => $input['prenomfr'] ?? null,
                ],
                'last_name' => [
                    'fr' => $input['nomfr'] ?? null,
                    'ar' => $input['nomar'] ?? null,
                    'en' => $input['nomfr'] ?? null,
                ],
                'email' => $input['email'] ?? null,
                'gender' => isset($input['gender']) ? (int) $input['gender'] : null,
                'phone' => $input['numtelephone'] ?? null,
                'birth_date' => $input['datenaissance'] ?? null,
                'birth_place' => $input['lieunaissance'] ?? null,
                'wilaya' => $input['wilaya'] ?? null,
                'dayra' => $input['dayra'] ?? null,
                'baladia' => $input['baladia'] ?? null,
            ],
            'guardian' => [
                'first_name' => [
                    'fr' => $input['prenomfrwali'] ?? null,
                    'ar' => $input['prenomarwali'] ?? null,
                    'en' => $input['prenomfrwali'] ?? null,
                ],
                'last_name' => [
                    'fr' => $input['nomfrwali'] ?? null,
                    'ar' => $input['nomarwali'] ?? null,
                    'en' => $input['nomfrwali'] ?? null,
                ],
                'relation' => $input['relationetudiant'] ?? null,
                'address' => $input['adressewali'] ?? null,
                'wilaya' => $input['wilayawali'] ?? null,
                'dayra' => $input['dayrawali'] ?? null,
                'baladia' => $input['baladiawali'] ?? null,
                'phone' => $input['numtelephonewali'] ?? null,
                'email' => $input['emailwali'] ?? null,
            ],
        ];
    }
}

