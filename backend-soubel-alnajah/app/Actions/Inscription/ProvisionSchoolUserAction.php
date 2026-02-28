<?php

namespace App\Actions\Inscription;

use App\Models\User;
use App\Services\UserOnboardingService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProvisionSchoolUserAction
{
    public function __construct(private UserOnboardingService $onboardingService)
    {
    }

    public function execute(array $name, ?string $email, int $schoolId, string $role, ?string $password = null): User
    {
        $hasManualPassword = is_string($password) && $password !== '';

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($hasManualPassword ? $password : Str::random(40)),
            'must_change_password' => !$hasManualPassword,
            'school_id' => $schoolId,
        ]);

        if (!$user->hasRole($role)) {
            $user->attachRole($role);
        }

        if (!$hasManualPassword) {
            $this->onboardingService->dispatchPasswordSetupLink($user);
        }

        return $user;
    }
}
