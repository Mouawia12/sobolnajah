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

    public function execute(array $name, ?string $email, int $schoolId, string $role): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'must_change_password' => true,
            'school_id' => $schoolId,
        ]);

        if (!$user->hasRole($role)) {
            $user->attachRole($role);
        }

        $this->onboardingService->dispatchPasswordSetupLink($user);

        return $user;
    }
}
