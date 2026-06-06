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

    public function execute(array $name, ?string $email, ?int $schoolId, string $role, ?string $password = null, bool $dispatchOnboarding = true, ?string $presetPasswordHash = null): User
    {
        $hasManualPassword = is_string($password) && $password !== '';

        // presetPasswordHash: تجزئة محسوبة مسبقاً للحسابات المؤقتة (الاستيراد الجماعي)
        // لتفادي تكلفة bcrypt لكل حساب — كلمة المرور عشوائية وغير قابلة للاستخدام على أي حال.
        $passwordHash = $hasManualPassword
            ? Hash::make($password)
            : ($presetPasswordHash ?? Hash::make(Str::random(40)));

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'must_change_password' => !$hasManualPassword,
            'school_id' => $schoolId,
        ]);

        if (!$user->hasRole($role)) {
            $user->attachRole($role);
        }

        if (!$hasManualPassword && $dispatchOnboarding) {
            $this->onboardingService->dispatchPasswordSetupLink($user);
        }

        return $user;
    }
}
