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
                // التخصص قابل للإسناد لاحقاً (أستاذ أُنشئ بحساب فقط).
                'specialization_id' => $payload['teacher']['specialization_id'],
                'name' => $payload['teacher']['name'],
                'gender' => $payload['teacher']['gender'],
                'joining_date' => $payload['teacher']['joining_date'],
                'address' => $payload['teacher']['address'],
            ]);

            if ($teacher->user) {
                $userData = [
                    'name' => $payload['user']['name'],
                    'email' => $payload['user']['email'],
                ];
                // المسؤول العام (بلا مؤسسة) لا يُفرغ مؤسسة الأستاذ عند التعديل.
                if ($schoolId) {
                    $userData['school_id'] = $schoolId;
                }

                if (!empty($payload['user']['password'])) {
                    $userData['password'] = Hash::make($payload['user']['password']);
                    $userData['must_change_password'] = false;
                }

                $teacher->user->update($userData);
            }
        });
    }
}
