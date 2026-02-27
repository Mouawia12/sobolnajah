<?php

namespace Tests\Unit\Inscription;

use App\Actions\Inscription\BuildLocalizedNameAction;
use App\Actions\Inscription\UpdateGuardianAccountAction;
use App\Models\Inscription\MyParent;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpdateGuardianAccountActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_guardian_user_profile_and_attaches_role(): void
    {
        Role::firstOrCreate(['name' => 'guardian']);

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create([
            'name' => ['fr' => 'Old', 'ar' => 'قديم', 'en' => 'Old'],
            'email' => 'old.guardian@example.test',
            'school_id' => null,
            'must_change_password' => false,
        ]);

        $guardian = MyParent::query()->create([
            'user_id' => $user->id,
            'prenomwali' => ['fr' => 'Old', 'ar' => 'قديم', 'en' => 'Old'],
            'nomwali' => ['fr' => 'Guardian', 'ar' => 'ولي', 'en' => 'Guardian'],
            'relationetudiant' => 'mother',
            'adressewali' => 'Address',
            'wilayawali' => 'Oran',
            'dayrawali' => 'Ain El Turk',
            'baladiawali' => 'Oran',
            'numtelephonewali' => '0550000002',
        ]);

        $action = new UpdateGuardianAccountAction(new BuildLocalizedNameAction());
        $action->execute($guardian, [
            'first_name' => ['fr' => 'Fatima', 'ar' => 'فاطمة'],
            'email' => 'new.guardian@example.test',
        ], $schoolId);

        $user->refresh();
        $this->assertSame('Fatima', $user->getTranslation('name', 'fr'));
        $this->assertSame('فاطمة', $user->getTranslation('name', 'ar'));
        $this->assertSame('new.guardian@example.test', $user->email);
        $this->assertSame($schoolId, (int) $user->school_id);
        $this->assertTrue($user->hasRole('guardian'));
    }

    public function test_it_throws_when_guardian_has_no_user(): void
    {
        $guardian = new MyParent();
        $guardian->setRelation('user', null);

        $action = new UpdateGuardianAccountAction(new BuildLocalizedNameAction());

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Guardian user account not found.');

        $action->execute($guardian, [
            'first_name' => ['fr' => 'Any', 'ar' => 'أي'],
            'email' => 'any@example.test',
        ], 1);
    }
}
