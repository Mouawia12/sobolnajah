<?php

namespace Tests\Unit\Inscription;

use App\Actions\Inscription\ProvisionSchoolUserAction;
use App\Models\Role;
use App\Services\UserOnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ProvisionSchoolUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_school_user_with_role_and_onboarding_link(): void
    {
        Role::firstOrCreate(['name' => 'teacher']);

        $onboardingService = Mockery::mock(UserOnboardingService::class);
        $onboardingService
            ->shouldReceive('dispatchPasswordSetupLink')
            ->once()
            ->andReturnTrue();

        $action = new ProvisionSchoolUserAction($onboardingService);
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = $action->execute(
            ['fr' => 'Teacher Name', 'ar' => 'اسم المعلم', 'en' => 'Teacher Name'],
            'teacher@example.test',
            $schoolId,
            'teacher'
        );

        $this->assertSame('teacher@example.test', $user->email);
        $this->assertSame($schoolId, (int) $user->school_id);
        $this->assertTrue((bool) $user->must_change_password);
        $this->assertTrue($user->hasRole('teacher'));
        $this->assertNotSame('', (string) $user->password);
        $this->assertNotSame('teacher@example.test', (string) $user->password);
    }
}
