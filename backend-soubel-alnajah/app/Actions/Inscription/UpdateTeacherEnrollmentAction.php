<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Teacher;
use Illuminate\Support\Facades\DB;

class UpdateTeacherEnrollmentAction
{
    public function execute(Teacher $teacher, array $input, ?int $schoolId): void
    {
        DB::transaction(function () use ($teacher, $input, $schoolId) {
            $teacher->update([
                'name' => [
                    'fr' => $input['name_teacherfr'] ?? null,
                    'ar' => $input['name_teacherar'] ?? null,
                    'en' => $input['name_teacherfr'] ?? null,
                ],
                'gender' => $input['gender'] ?? null,
                'joining_date' => $input['joining_date'] ?? null,
                'address' => $input['address'] ?? null,
            ]);

            if ($teacher->user) {
                $teacher->user->update([
                    'name' => [
                        'fr' => $input['name_teacherfr'] ?? null,
                        'ar' => $input['name_teacherar'] ?? null,
                        'en' => $input['name_teacherfr'] ?? null,
                    ],
                    'email' => $input['email'] ?? null,
                    'school_id' => $schoolId,
                ]);
            }
        });
    }
}
