<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateTeacherEnrollmentAction
{
    public function __construct(private BuildTeacherEnrollmentPayloadAction $buildTeacherEnrollmentPayloadAction)
    {
    }

    public function execute(Teacher $teacher, array $input, ?int $schoolId): void
    {
        DB::transaction(function () use ($teacher, $input, $schoolId) {
            $payload = $this->buildTeacherEnrollmentPayloadAction->execute($input);

            $teacher->update([
                'name' => $payload['teacher']['name'],
                'gender' => $payload['teacher']['gender'],
                'joining_date' => $payload['teacher']['joining_date'],
                'address' => $payload['teacher']['address'],
            ]);

            if ($teacher->user) {
                $userData = [
                    'name' => $payload['user']['name'],
                    'email' => $payload['user']['email'],
                    'school_id' => $schoolId,
                ];

                if (!empty($payload['user']['password'])) {
                    $userData['password'] = Hash::make($payload['user']['password']);
                    $userData['must_change_password'] = false;
                }

                $teacher->user->update($userData);
            }
        });
    }
}
