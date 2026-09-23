<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\RoleMenuSection;
use App\Models\User;
use App\Services\MenuAccessService;
use App\Support\MenuCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class RolePermissionTest extends TestCase
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

        foreach (['admin', 'accountant', 'teacher'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_admin_sees_all_sections(): void
    {
        $admin = $this->admin();
        $allowed = app(MenuAccessService::class)->allowedSections($admin);
        $this->assertEqualsCanonicalizing(MenuCatalog::keys(), $allowed);
    }

    public function test_role_sees_only_granted_sections(): void
    {
        $role = Role::firstOrCreate(['name' => 'accountant']);
        RoleMenuSection::create(['role_id' => $role->id, 'section_key' => 'finance']);

        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('accountant');

        $allowed = app(MenuAccessService::class)->allowedSections($user->fresh());
        $this->assertSame(['finance'], $allowed);
    }

    public function test_sidebar_shows_granted_and_hides_others_for_accountant(): void
    {
        $role = Role::firstOrCreate(['name' => 'accountant']);
        RoleMenuSection::create(['role_id' => $role->id, 'section_key' => 'finance']);
        MenuAccessService::bustCache();

        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('accountant');

        $response = $this->actingAs($user)->get(route('accountant.dashboard'));
        $response->assertStatus(200);
        $response->assertSee(trans('main_sidebar.finance'));      // قسم ممنوح
        $response->assertDontSee('الطلاب', false);                 // قسم غير ممنوح
    }

    public function test_teacher_sidebar_shows_defaults_only(): void
    {
        Role::firstOrCreate(['name' => 'teacher']);
        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('teacher');

        $response = $this->actingAs($user)->get(route('teacher.dashboard'));
        $response->assertStatus(200);
        $response->assertSee(trans('academic.assessments'));   // قسم ممنوح افتراضياً
        $response->assertDontSee(trans('main_sidebar.finance')); // قسم غير ممنوح
        $response->assertDontSee('الطلاب', false);
    }

    public function test_admin_can_create_role(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson(route('roles.store'), [
            'display_name' => 'الناظر العام',
            'name' => 'censeur',
        ]);
        $response->assertStatus(200)->assertJson(['ok' => true]);
        $this->assertDatabaseHas('roles', ['name' => 'censeur', 'display_name' => 'الناظر العام']);
    }

    public function test_save_permissions_updates_grants_and_affects_sidebar(): void
    {
        $admin = $this->admin();
        $custom = Role::create(['name' => 'librarian', 'display_name' => 'أمين مكتبة']);

        $this->actingAs($admin)->postJson(route('roles.permissions.save'), [
            'perms' => [$custom->id => ['content', 'communication']],
        ])->assertStatus(200)->assertJson(['ok' => true]);

        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('librarian');

        $allowed = app(MenuAccessService::class)->allowedSections($user->fresh());
        $this->assertEqualsCanonicalizing(['content', 'communication'], $allowed);
    }

    public function test_cannot_delete_core_role(): void
    {
        $admin = $this->admin();
        $accountant = Role::firstOrCreate(['name' => 'accountant']);

        $response = $this->actingAs($admin)->deleteJson(route('roles.destroy', $accountant->id));
        $response->assertStatus(422)->assertJson(['ok' => false]);
        $this->assertDatabaseHas('roles', ['name' => 'accountant']);
    }

    public function test_can_delete_custom_role(): void
    {
        $admin = $this->admin();
        $custom = Role::create(['name' => 'temp_role', 'display_name' => 'مؤقت']);

        $this->actingAs($admin)->deleteJson(route('roles.destroy', $custom->id))->assertStatus(200)->assertJson(['ok' => true]);
        $this->assertDatabaseMissing('roles', ['name' => 'temp_role']);
    }

    public function test_admin_can_create_user_with_single_role_via_ajax(): void
    {
        $admin = $this->admin();
        Role::firstOrCreate(['name' => 'supervisor']);

        $response = $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'ناظر جديد',
            'email' => 'new-staff@example.test',
            'password' => 'Secret123',
            'role' => 'supervisor',
        ]);
        $response->assertStatus(200)->assertJson(['ok' => true]);

        $user = User::query()->where('email', 'new-staff@example.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('supervisor'));
        $this->assertTrue((bool) $user->must_change_password);
    }

    public function test_users_data_returns_json_list(): void
    {
        $admin = $this->admin();
        User::factory()->create(['must_change_password' => false, 'email' => 'listme@example.test']);

        $response = $this->actingAs($admin)->getJson(route('roles.users.data', ['q' => 'listme']));
        $response->assertStatus(200)->assertJson(['ok' => true]);
        $this->assertStringContainsString('listme@example.test', $response->getContent());
    }

    public function test_update_user_role_via_ajax_replaces_existing_role(): void
    {
        $admin = $this->admin();
        Role::firstOrCreate(['name' => 'accountant']);
        Role::firstOrCreate(['name' => 'teacher']);

        $target = User::factory()->create(['must_change_password' => false]);
        $target->attachRole('teacher');

        $this->actingAs($admin)->postJson(route('roles.users.roles', $target->id), [
            'role' => 'accountant',
        ])->assertStatus(200)->assertJson(['ok' => true]);

        $target->refresh();
        $this->assertTrue($target->hasRole('accountant'));
        // دور واحد فقط: الدور القديم يُستبدل ولا يتراكم.
        $this->assertFalse($target->hasRole('teacher'));
        $this->assertCount(1, $target->roles);
    }

    public function test_update_user_role_with_empty_clears_roles(): void
    {
        $admin = $this->admin();
        Role::firstOrCreate(['name' => 'teacher']);

        $target = User::factory()->create(['must_change_password' => false]);
        $target->attachRole('teacher');

        $this->actingAs($admin)->postJson(route('roles.users.roles', $target->id), [
            'role' => '',
        ])->assertStatus(200)->assertJson(['ok' => true]);

        $this->assertCount(0, $target->fresh()->roles);
    }

    public function test_cannot_delete_self_via_ajax(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->deleteJson(route('roles.users.destroy', $admin->id))
            ->assertStatus(422)->assertJson(['ok' => false]);
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_non_admin_cannot_access_roles_page(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('teacher');

        $response = $this->actingAs($user)->get(route('roles.index'));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $admin->attachRole('admin');

        return $admin;
    }
}
