<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class AccountsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LocalizationRedirect::class,
            LocaleViewPath::class,
        ]);

        foreach (['admin', 'employee', 'supervisor', 'teacher'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_admin_sees_accounts_list(): void
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $admin->attachRole('admin');
        $target = User::factory()->create(['must_change_password' => false, 'email' => 'findme@example.test']);

        $response = $this->actingAs($admin)->get(route('accounts.index'));
        $response->assertStatus(200);
        $response->assertSee('findme@example.test');
    }

    public function test_admin_can_reset_password_and_forces_change(): void
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $admin->attachRole('admin');
        $target = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($admin)->post(route('accounts.reset', $target->id), [
            'new_password' => 'NewPass123',
        ]);

        $response->assertStatus(302);
        $target->refresh();
        $this->assertTrue(Hash::check('NewPass123', $target->password));
        $this->assertTrue((bool) $target->must_change_password);
    }

    public function test_admin_can_update_single_role(): void
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $admin->attachRole('admin');
        $target = User::factory()->create(['must_change_password' => false]);

        $this->actingAs($admin)->post(route('accounts.roles', $target->id), [
            'role' => 'supervisor',
        ])->assertStatus(302);

        $target->refresh();
        $this->assertTrue($target->hasRole('supervisor'));
        $this->assertCount(1, $target->roles);
    }

    public function test_updating_role_replaces_previous_role(): void
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $admin->attachRole('admin');

        $target = User::factory()->create(['must_change_password' => false]);
        $this->actingAs($admin)->post(route('accounts.roles', $target->id), ['role' => 'supervisor'])->assertStatus(302);
        $this->actingAs($admin)->post(route('accounts.roles', $target->id), ['role' => 'employee'])->assertStatus(302);

        $target->refresh();
        $this->assertTrue($target->hasRole('employee'));
        $this->assertFalse($target->hasRole('supervisor'));
        $this->assertCount(1, $target->roles);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $admin->attachRole('admin');

        $response = $this->actingAs($admin)->delete(route('accounts.destroy', $admin->id));
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_bound_admin_cannot_manage_account_from_another_school(): void
    {
        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => $schoolA]);
        $admin->attachRole('admin');
        $foreign = User::factory()->create(['must_change_password' => false, 'school_id' => $schoolB]);

        $response = $this->actingAs($admin)->post(route('accounts.reset', $foreign->id), [
            'new_password' => 'Whatever123',
        ]);
        $response->assertStatus(404);
    }

    public function test_non_admin_cannot_access_accounts(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('teacher');

        $response = $this->actingAs($user)->get(route('accounts.index'));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }
}
