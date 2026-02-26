<?php

namespace App\Actions\Inscription;

use App\Models\Inscription\Teacher;
use App\Models\User;
use App\Services\UserOnboardingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTeacherEnrollmentAction
{
    public function __construct(private UserOnboardingService $onboardingService)
    {
    }

    public function execute(array $input, int $schoolId): void
    {
        DB::transaction(function () use ($input, $schoolId) {
            $user = User::create([
                'name' => [
                    'fr' => $input['name_teacherfr'] ?? null,
                    'ar' => $input['name_teacherar'] ?? null,
                    'en' => $input['name_teacherfr'] ?? null,
                ],
                'email' => $input['email'] ?? null,
                'password' => Hash::make(Str::random(40)),
                'must_change_password' => true,
                'school_id' => $schoolId,
            ]);

            $user->attachRole('teacher');
            $this->onboardingService->dispatchPasswordSetupLink($user);

            Teacher::create([
                'user_id' => $user->id,
                'specialization_id' => $input['specialization_id'] ?? null,
                'name' => [
                    'fr' => $input['name_teacherfr'] ?? null,
                    'ar' => $input['name_teacherar'] ?? null,
                    'en' => $input['name_teacherfr'] ?? null,
                ],
                'gender' => $input['gender'] ?? null,
                'joining_date' => $input['joining_date'] ?? null,
                'address' => $input['address'] ?? null,
            ]);
        });
    }
}
