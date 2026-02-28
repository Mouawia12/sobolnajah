<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Teacher;
use Illuminate\Support\Facades\DB;

class CreateTeacherEnrollmentAction
{
    public function __construct(
        private BuildTeacherEnrollmentPayloadAction $buildTeacherEnrollmentPayloadAction,
        private ProvisionSchoolUserAction $provisionSchoolUserAction
    )
    {
    }

    public function execute(array $input, int $schoolId): void
    {
        DB::transaction(function () use ($input, $schoolId) {
            $payload = $this->buildTeacherEnrollmentPayloadAction->execute($input);

            $user = $this->provisionSchoolUserAction->execute(
                $payload['user']['name'],
                $payload['user']['email'],
                $schoolId,
                'teacher',
                $payload['user']['password']
            );

            Teacher::create([
                'user_id' => $user->id,
                'specialization_id' => $payload['teacher']['specialization_id'],
                'name' => $payload['teacher']['name'],
                'gender' => $payload['teacher']['gender'],
                'joining_date' => $payload['teacher']['joining_date'],
                'address' => $payload['teacher']['address'],
            ]);
        });
    }
}
