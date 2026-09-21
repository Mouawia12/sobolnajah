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

        $response = $this->actingAs($admin)->post(route('roles.store'), [
            'display_name' => 'الناظر العام',
            'name' => 'censeur',
        ]);
        $response->assertStatus(302);
        $this->assertDatabaseHas('roles', ['name' => 'censeur', 'display_name' => 'الناظر العام']);
    }

    public function test_save_permissions_updates_grants_and_affects_sidebar(): void
    {
        $admin = $this->admin();
        $custom = Role::create(['name' => 'librarian', 'display_name' => 'أمين مكتبة']);

        $this->actingAs($admin)->post(route('roles.permissions.save'), [
            'perms' => [$custom->id => ['content', 'communication']],
        ])->assertStatus(302);

        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('librarian');

        $allowed = app(MenuAccessService::class)->allowedSections($user->fresh());
        $this->assertEqualsCanonicalizing(['content', 'communication'], $allowed);
    }

    public function test_cannot_delete_core_role(): void
    {
        $admin = $this->admin();
        $accountant = Role::firstOrCreate(['name' => 'accountant']);

        $response = $this->actingAs($admin)->delete(route('roles.destroy', $accountant->id));
        $response->assertSessionHasErrors('error');
        $this->assertDatabaseHas('roles', ['name' => 'accountant']);
    }

    public function test_can_delete_custom_role(): void
    {
        $admin = $this->admin();
        $custom = Role::create(['name' => 'temp_role', 'display_name' => 'مؤقت']);

        $this->actingAs($admin)->delete(route('roles.destroy', $custom->id))->assertStatus(302);
        $this->assertDatabaseMissing('roles', ['name' => 'temp_role']);
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
