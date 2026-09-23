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
        // بريد الدخول لا يتغيّر عبر التسجيل (منع الاستيلاء على الحساب).
        $this->assertSame('old.guardian@example.test', $user->email);
        // المؤسسة تُسند فقط إن كانت فارغة.
        $this->assertSame($schoolId, (int) $user->school_id);
        $this->assertTrue($user->hasRole('guardian'));
    }

    public function test_it_does_not_move_guardian_from_existing_school(): void
    {
        Role::firstOrCreate(['name' => 'guardian']);

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $user = User::factory()->create([
            'email' => 'victim@example.test',
            'school_id' => $schoolA,
            'must_change_password' => false,
        ]);
        $guardian = MyParent::query()->create([
            'user_id' => $user->id,
            'prenomwali' => ['fr' => 'V', 'ar' => 'و', 'en' => 'V'],
            'nomwali' => ['fr' => 'G', 'ar' => 'ج', 'en' => 'G'],
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => '0550000009',
        ]);

        (new UpdateGuardianAccountAction(new BuildLocalizedNameAction()))->execute($guardian, [
            'first_name' => ['fr' => 'X', 'ar' => 'س'],
            'email' => 'attacker@example.test',
        ], $schoolB);

        $user->refresh();
        $this->assertSame('victim@example.test', $user->email);
        $this->assertSame($schoolA, (int) $user->school_id);
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
