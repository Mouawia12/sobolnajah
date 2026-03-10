<?php

namespace App\Actions\Inscription;

class BuildTeacherEnrollmentPayloadAction
{
    public function __construct(private BuildLocalizedNameAction $buildLocalizedNameAction)
    {
    }

    public function execute(array $input): array
    {
        $nameFr = $input['name_teacherfr'] ?? null;
        $nameAr = $input['name_teacherar'] ?? null;

        $name = $this->buildLocalizedNameAction->execute(
            $nameFr,
            $nameAr,
            $nameFr
        );

        return [
            'user' => [
                'name' => $name,
                'email' => $input['email'] ?? null,
                'password' => $input['password'] ?? null,
            ],
            'teacher' => [
                'specialization_id' => $input['specialization_id'] ?? null,
                'name' => $name,
                'gender' => $input['gender'] ?? null,
                'joining_date' => $input['joining_date'] ?? null,
                'address' => $input['address'] ?? null,
            ],
        ];
    }
}
