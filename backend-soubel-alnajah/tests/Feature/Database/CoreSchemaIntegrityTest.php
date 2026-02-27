<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CoreSchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_studentinfos_user_id_is_unique(): void
    {
        [$sectionId, $parentId] = $this->createSchoolHierarchyWithGuardian();

        $studentUser = User::factory()->create();

        DB::table('studentinfos')->insert([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'nom' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550000101,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('studentinfos')->insert([
            'user_id' => $studentUser->id,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'gender' => 1,
            'prenom' => json_encode(['fr' => 'C', 'ar' => 'ج', 'en' => 'C']),
            'nom' => json_encode(['fr' => 'D', 'ar' => 'د', 'en' => 'D']),
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'datenaissance' => '2013-01-01',
            'numtelephone' => 550000102,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_notifications_data_can_repeat_for_different_rows(): void
    {
        $user = User::factory()->create();
        $payload = json_encode(['message' => 'shared-payload']);

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\StudentSchoolCertificateNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => $payload,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'App\\Notifications\\StudentSchoolCertificateNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => $payload,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(2, DB::table('notifications')->where('notifiable_id', $user->id)->count());
    }

    public function test_legacy_messages_table_is_removed(): void
    {
        $this->assertFalse(Schema::hasTable('messages'));
    }

    private function createSchoolHierarchyWithGuardian(): array
    {
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

        $guardianUser = User::factory()->create(['school_id' => $schoolId]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => 'Guardian', 'ar' => 'وصي', 'en' => 'Guardian']),
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'numtelephonewali' => 550000100,
            'user_id' => $guardianUser->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$sectionId, $parentId];
    }
}
