<?php

namespace App\Actions\Inscription;

class BuildTeacherEnrollmentPayloadAction
{
    public function __construct(private BuildLocalizedNameAction $buildLocalizedNameAction)
    {
    }

    public function execute(array $input): array
    {
        $name = $this->buildLocalizedNameAction->execute(
            $input['name_teacherfr'] ?? null,
            $input['name_teacherar'] ?? null
        );

        return [
            'user' => [
                'name' => $name,
                'email' => $input['email'] ?? null,
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
