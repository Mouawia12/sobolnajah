<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use App\Notifications\OnboardingDeliveryFailedNotification;
use App\Services\UserOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class OnboardingDeliveryChannelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_notifies_school_admins_when_onboarding_link_cannot_be_sent(): void
    {
        config(['onboarding.notify_admins_on_failure' => true]);
        Role::firstOrCreate(['name' => 'admin']);

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School', 'ar' => 'مدرسة', 'en' => 'School']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);
        $admin->attachRole('admin');

        $target = User::factory()->create([
            'school_id' => $schoolId,
            'email' => 'new-user@example.test',
            'must_change_password' => true,
        ]);

        Password::shouldReceive('broker->sendResetLink')
            ->once()
            ->andReturn('passwords.throttled');

        /** @var UserOnboardingService $service */
        $service = app(UserOnboardingService::class);
        $result = $service->dispatchPasswordSetupLink($target);

        $this->assertFalse($result);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'type' => OnboardingDeliveryFailedNotification::class,
        ]);
    }
}
