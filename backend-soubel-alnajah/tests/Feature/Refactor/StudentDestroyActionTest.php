<?php

namespace Tests\Feature\Refactor;

use App\Models\Inscription\StudentInfo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentDestroyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_deletes_student_and_guardian_when_no_remaining_children(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrapSchoolAdminAndSection();

        $studentUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);
        $guardianUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);

        $guardianId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Guardian', 'ar' => 'ولي', 'en' => 'Guardian']),
            'nomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000001,
            'user_id' => $guardianUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $guardianId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'Student', 'ar' => 'تلميذ', 'en' => 'Student']),
            'nom' => json_encode(['fr' => 'Last', 'ar' => 'لقب', 'en' => 'Last']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000010,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\SystemNotice',
                'notifiable_type' => User::class,
                'notifiable_id' => $studentUser->id,
                'data' => '{"message":"student_notice_' . Str::uuid() . '"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\SystemNotice',
                'notifiable_type' => User::class,
                'notifiable_id' => $guardianUser->id,
                'data' => '{"message":"guardian_notice_' . Str::uuid() . '"}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)->delete(route('Students.destroy', ['Student' => $studentId]));
        $response->assertStatus(302);

        $this->assertDatabaseMissing('studentinfos', ['id' => $studentId]);
        $this->assertDatabaseMissing('users', ['id' => $studentUser->id]);
        $this->assertDatabaseMissing('my_parents', ['id' => $guardianId]);
        $this->assertDatabaseMissing('users', ['id' => $guardianUser->id]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $studentUser->id]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $guardianUser->id]);
    }

    public function test_destroy_keeps_guardian_when_it_has_another_child(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrapSchoolAdminAndSection();

        $studentUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);
        $siblingUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);
        $guardianUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);

        $guardianId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Guardian', 'ar' => 'ولي', 'en' => 'Guardian']),
            'nomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'relationetudiant' => 'mother',
            'adressewali' => 'Address',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000001,
            'user_id' => $guardianUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $guardianId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'StudentA', 'ar' => 'تلميذ أ', 'en' => 'StudentA']),
            'nom' => json_encode(['fr' => 'LastA', 'ar' => 'لقب أ', 'en' => 'LastA']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000010,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('studentinfos')->insert([
            'user_id' => $siblingUser->id,
            'section_id' => $sectionId,
            'parent_id' => $guardianId,
            'gender' => 0,
            'prenom' => json_encode(['fr' => 'StudentB', 'ar' => 'تلميذ ب', 'en' => 'StudentB']),
            'nom' => json_encode(['fr' => 'LastB', 'ar' => 'لقب ب', 'en' => 'LastB']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2013-01-01',
            'numtelephone' => 550000011,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('Students.destroy', ['Student' => $studentId]));
        $response->assertStatus(302);

        $this->assertDatabaseMissing('studentinfos', ['id' => $studentId]);
        $this->assertDatabaseMissing('users', ['id' => $studentUser->id]);
        $this->assertDatabaseHas('my_parents', ['id' => $guardianId]);
        $this->assertDatabaseHas('users', ['id' => $guardianUser->id]);
    }

    private function bootstrapSchoolAdminAndSection(): array
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

        $admin->update(['school_id' => $schoolId]);

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

        return [$admin, $schoolId, $sectionId];
    }
}
