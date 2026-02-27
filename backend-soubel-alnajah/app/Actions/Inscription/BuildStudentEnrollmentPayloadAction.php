<?php

namespace App\Actions\Inscription;

class BuildStudentEnrollmentPayloadAction
{
    public function __construct(private BuildLocalizedNameAction $buildLocalizedNameAction)
    {
    }

    /**
     * @return array{student: array<string, mixed>, guardian: array<string, mixed>}
     */
    public function execute(array $input): array
    {
        return [
            'student' => [
                'first_name' => $this->buildLocalizedNameAction->execute(
                    $input['prenomfr'] ?? null,
                    $input['prenomar'] ?? null
                ),
                'last_name' => $this->buildLocalizedNameAction->execute(
                    $input['nomfr'] ?? null,
                    $input['nomar'] ?? null
                ),
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
                'first_name' => $this->buildLocalizedNameAction->execute(
                    $input['prenomfrwali'] ?? null,
                    $input['prenomarwali'] ?? null
                ),
                'last_name' => $this->buildLocalizedNameAction->execute(
                    $input['nomfrwali'] ?? null,
                    $input['nomarwali'] ?? null
                ),
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
