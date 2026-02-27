<?php

namespace Tests\Feature\Refactor;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class FunctionNotificationActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_endpoint_creates_database_notification_for_authenticated_user(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('notify', ['id' => $targetUser->id]), [
            'year' => '2026-' . Str::random(4),
            'namefr' => 'Demande Certificat',
            'namear' => 'طلب شهادة',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'notifiable_type' => User::class,
            'type' => \App\Notifications\StudentSchoolCertificateNotification::class,
        ]);
    }

    public function test_notify_endpoint_rejects_non_existing_target_user_id(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $response = $this->actingAs($admin)->post(route('notify', ['id' => 999999]), [
            'year' => '2026-' . Str::random(4),
            'namefr' => 'Demande Certificat',
            'namear' => 'طلب شهادة',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('id');
    }

    public function test_mark_as_read_marks_owned_notification_as_read(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G1', 'ar' => 'م1', 'en' => 'G1']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C1', 'ar' => 'ق1', 'en' => 'C1']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => 'Last', 'ar' => 'لقب', 'en' => 'Last']),
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000123,
            'user_id' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('studentinfos')->insert([
            'user_id' => $admin->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Admin', 'ar' => 'ادمن', 'en' => 'Admin']),
            'nom' => json_encode(['fr' => 'User', 'ar' => 'مستخدم', 'en' => 'User']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2000-01-01',
            'numtelephone' => 550000456,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => \App\Notifications\StudentSchoolCertificateNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->id,
            'data' => '{"namefr":"Test","namear":"اختبار","email":"x@example.test","year":"2026-' . Str::random(4) . '"}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('markAsRead', ['id' => $notificationId]));
        $response->assertStatus(200);

        $readAt = DB::table('notifications')->where('id', $notificationId)->value('read_at');
        $this->assertNotNull($readAt);
    }

    public function test_mark_as_read_rejects_invalid_notification_id_format(): void
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $response = $this->actingAs($admin)->post(route('markAsRead', ['id' => 'not-a-uuid']));

        $response->assertStatus(302);
        $response->assertSessionHasErrors('id');
    }
}
