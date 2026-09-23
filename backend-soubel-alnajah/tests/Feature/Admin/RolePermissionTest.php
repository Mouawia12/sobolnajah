<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\RoleMenuSection;
use App\Models\User;
use App\Services\MenuAccessService;
use App\Support\MenuCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        foreach (['admin', 'accountant', 'teacher', 'student', 'guardian', 'supervisor'] as $role) {
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

    public function test_create_teacher_via_modal_without_specialization_creates_profile(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'أستاذ بلا تخصص',
            'email' => 'teacher-nospec@example.test',
            'password' => 'Secret123',
            'role' => 'teacher',
        ])->assertStatus(200)->assertJson(['ok' => true]);

        $user = User::query()->where('email', 'teacher-nospec@example.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('teacher'));
        // سجل المعلّم يُنشأ حتى بلا تخصص (لا خطأ عند الدخول).
        $this->assertDatabaseHas('teachers', ['user_id' => $user->id, 'specialization_id' => null]);
    }

    public function test_create_teacher_via_modal_with_specialization(): void
    {
        $admin = $this->admin();
        $specId = DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'أستاذ رياضيات',
            'email' => 'teacher-spec@example.test',
            'password' => 'Secret123',
            'role' => 'teacher',
            'specialization_id' => $specId,
            'gender' => 1,
        ])->assertStatus(200)->assertJson(['ok' => true]);

        $user = User::query()->where('email', 'teacher-spec@example.test')->first();
        $this->assertDatabaseHas('teachers', ['user_id' => $user->id, 'specialization_id' => $specId, 'gender' => 1]);
    }

    public function test_create_guardian_via_modal_creates_parent_profile(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'ولي أمر',
            'email' => 'guardian-modal@example.test',
            'password' => 'Secret123',
            'role' => 'guardian',
            'guardian_relation' => 'أب',
            'guardian_phone' => '0550000000',
        ])->assertStatus(200)->assertJson(['ok' => true]);

        $user = User::query()->where('email', 'guardian-modal@example.test')->first();
        $this->assertTrue($user->hasRole('guardian'));
        $this->assertDatabaseHas('my_parents', ['user_id' => $user->id]);
    }

    public function test_create_student_via_modal_requires_guardian_and_section(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'تلميذ',
            'email' => 'student-missing@example.test',
            'password' => 'Secret123',
            'role' => 'student',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('users', ['email' => 'student-missing@example.test']);
    }

    public function test_create_student_via_modal_creates_student_profile(): void
    {
        $admin = $this->admin();
        [$sectionId, $guardianUserId] = $this->seedSectionAndGuardian();

        $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'تلميذ جديد',
            'email' => 'student-modal@example.test',
            'password' => 'Secret123',
            'role' => 'student',
            'guardian_user_id' => $guardianUserId,
            'section_id' => $sectionId,
            'gender' => 1,
            'student_birth_date' => '2012-05-01',
        ])->assertStatus(200)->assertJson(['ok' => true]);

        $user = User::query()->where('email', 'student-modal@example.test')->first();
        $this->assertTrue($user->hasRole('student'));
        $this->assertDatabaseHas('studentinfos', ['user_id' => $user->id, 'section_id' => $sectionId]);
    }

    public function test_create_student_via_modal_rejects_non_guardian_user(): void
    {
        $admin = $this->admin();
        [$sectionId] = $this->seedSectionAndGuardian();
        $notGuardian = User::factory()->create(['must_change_password' => false]);

        $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'تلميذ',
            'email' => 'student-bad-guardian@example.test',
            'password' => 'Secret123',
            'role' => 'student',
            'guardian_user_id' => $notGuardian->id,
            'section_id' => $sectionId,
            'student_birth_date' => '2012-05-01',
        ])->assertStatus(422)->assertJsonValidationErrors('guardian_user_id');

        $this->assertDatabaseMissing('users', ['email' => 'student-bad-guardian@example.test']);
    }

    public function test_create_student_via_modal_requires_birth_date(): void
    {
        $admin = $this->admin();
        [$sectionId, $guardianUserId] = $this->seedSectionAndGuardian();

        $this->actingAs($admin)->postJson(route('roles.users.store'), [
            'name' => 'تلميذ',
            'email' => 'student-no-birth@example.test',
            'password' => 'Secret123',
            'role' => 'student',
            'guardian_user_id' => $guardianUserId,
            'section_id' => $sectionId,
        ])->assertStatus(422)->assertJsonValidationErrors('student_birth_date');
    }

    public function test_changing_role_to_teacher_creates_teacher_profile(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['must_change_password' => false]);

        $this->actingAs($admin)->postJson(route('roles.users.roles', $target->id), ['role' => 'teacher'])
            ->assertStatus(200)->assertJson(['ok' => true]);

        $this->assertTrue($target->fresh()->hasRole('teacher'));
        $this->assertDatabaseHas('teachers', ['user_id' => $target->id]);
    }

    public function test_changing_role_to_student_without_profile_is_rejected(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['must_change_password' => false]);
        $target->attachRole('accountant');

        $this->actingAs($admin)->postJson(route('roles.users.roles', $target->id), ['role' => 'student'])
            ->assertStatus(422);

        $this->assertTrue($target->fresh()->hasRole('accountant'));
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('roles.users.roles', $admin->id), ['role' => 'accountant'])
            ->assertStatus(422)->assertJson(['ok' => false]);

        $this->assertTrue($admin->fresh()->hasRole('admin'));
    }

    public function test_supervisor_home_redirects_to_first_accessible_section(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('supervisor');

        // لا يوجّه لـ employees.index المحمي بدور admin (كان يعطي 403)، بل لحضور الطاقم.
        $this->assertFalse(app(MenuAccessService::class)->canAccessRoute($user, 'employees.index'));

        $this->actingAs($user)->get(route('home'))
            ->assertRedirect(route('staff-attendance.record', ['teachers']));
    }

    public function test_custom_role_without_sections_sees_welcome_page(): void
    {
        Role::firstOrCreate(['name' => 'librarian']);
        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('librarian');

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee(trans('roles.welcome_no_sections'));
    }

    /** @return array{0:int,1:int} [sectionId, guardianUserId] */
    private function seedSectionAndGuardian(): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'مدرسة', 'en' => 'S']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId, 'name_grade' => json_encode(['fr' => 'G', 'ar' => 'م', 'en' => 'G']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'name_class' => json_encode(['fr' => 'C', 'ar' => 'ق', 'en' => 'C']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S', 'ar' => 'ف', 'en' => 'S']), 'Status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $guardianUser = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $guardianUser->attachRole('guardian');
        DB::table('my_parents')->insert([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550000123,
            'user_id' => $guardianUser->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$sectionId, $guardianUser->id];
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => null]);
        $admin->attachRole('admin');

        return $admin;
    }
}
