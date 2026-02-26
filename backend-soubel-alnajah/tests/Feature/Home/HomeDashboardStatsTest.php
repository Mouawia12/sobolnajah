<?php

namespace Tests\Feature\Home;

use App\Http\Controllers\HomeController;
use App\Models\Inscription\StudentInfo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeDashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_stats_are_school_scoped_and_aggregated(): void
    {
        [$schoolA, $gradeA, $classA, $sectionA] = $this->createSchoolHierarchy('A');
        [$schoolB, $gradeB, $classB, $sectionB] = $this->createSchoolHierarchy('B');

        $admin = User::factory()->create([
            'must_change_password' => false,
            'school_id' => $schoolA,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $this->createStudentWithParent($schoolA, $sectionA, 1, 'Ali');
        $this->createStudentWithParent($schoolA, $sectionA, 0, 'Mina');
        $this->createStudentWithParent($schoolB, $sectionB, 1, 'Outside');

        $this->createInscription($schoolA, $gradeA, $classA, 'accept');
        $this->createInscription($schoolA, $gradeA, $classA, 'procec');
        $this->createInscription($schoolB, $gradeB, $classB, 'procec');

        $senderA = User::factory()->create([
            'must_change_password' => false,
            'school_id' => $schoolA,
        ]);
        $senderB = User::factory()->create([
            'must_change_password' => false,
            'school_id' => $schoolB,
        ]);

        $roomId = DB::table('chat_rooms')->insertGetId([
            'name' => 'Room 1',
            'is_group' => 1,
            'created_by' => $admin->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('chat_messages')->insert([
            [
                'chat_room_id' => $roomId,
                'user_id' => $senderA->id,
                'body' => 'local',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'chat_room_id' => $roomId,
                'user_id' => $senderB->id,
                'body' => 'external',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->be($admin);
        $view = app(HomeController::class)->index();
        $stats = $view->getData()['stats'];

        $this->assertSame(2, $stats['total_students']);
        $this->assertSame(1, $stats['male_students']);
        $this->assertSame(1, $stats['female_students']);
        $this->assertSame(2, $stats['total_guardians']);
        $this->assertSame(1, $stats['pending_inscriptions']);
        $this->assertSame(1, $stats['messages_today']);
    }

    public function test_dashboard_chart_cache_is_invalidated_when_student_changes(): void
    {
        [$schoolA, , , $sectionA] = $this->createSchoolHierarchy('A');

        $admin = User::factory()->create([
            'must_change_password' => false,
            'school_id' => $schoolA,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $studentId = $this->createStudentWithParent($schoolA, $sectionA, 1, 'Cache');

        $this->be($admin);
        app(HomeController::class)->index();

        $locale = app()->getLocale();
        $byGradeKey = "home:school:{$schoolA}:locale:{$locale}:students-by-grade";
        $monthlyKey = "home:school:{$schoolA}:locale:{$locale}:students-monthly";

        $this->assertTrue(Cache::has($byGradeKey));
        $this->assertTrue(Cache::has($monthlyKey));

        $student = StudentInfo::query()->findOrFail($studentId);
        $student->update([
            'numtelephone' => 552000000 + random_int(1, 999),
        ]);

        $this->assertFalse(Cache::has($byGradeKey));
        $this->assertFalse(Cache::has($monthlyKey));
    }

    private function createSchoolHierarchy(string $suffix): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'School ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G' . $suffix, 'ar' => 'G' . $suffix, 'en' => 'G' . $suffix]),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'N', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C' . $suffix, 'ar' => 'C' . $suffix, 'en' => 'C' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classId,
            'name_section' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'S' . $suffix, 'en' => 'S' . $suffix]),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $gradeId, $classId, $sectionId];
    }

    private function createStudentWithParent(int $schoolId, int $sectionId, int $gender, string $suffix): int
    {
        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Parent ' . $suffix, 'ar' => 'Parent ' . $suffix, 'en' => 'Parent ' . $suffix]),
            'email' => 'parent-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'Parent', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => $suffix, 'ar' => $suffix, 'en' => $suffix]),
            'relationetudiant' => json_encode(['fr' => 'Father', 'ar' => 'Father', 'en' => 'Father']),
            'adressewali' => json_encode(['fr' => 'Address', 'ar' => 'Address', 'en' => 'Address']),
            'wilayawali' => json_encode(['fr' => 'Wilaya', 'ar' => 'Wilaya', 'en' => 'Wilaya']),
            'dayrawali' => json_encode(['fr' => 'Dayra', 'ar' => 'Dayra', 'en' => 'Dayra']),
            'baladiawali' => json_encode(['fr' => 'Baladia', 'ar' => 'Baladia', 'en' => 'Baladia']),
            'numtelephonewali' => 554000000 + random_int(1, 999),
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Student ' . $suffix, 'ar' => 'Student ' . $suffix, 'en' => 'Student ' . $suffix]),
            'email' => 'student-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserId,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'prenom' => json_encode(['fr' => $suffix, 'ar' => $suffix, 'en' => $suffix]),
            'nom' => json_encode(['fr' => 'Student', 'ar' => 'Student', 'en' => 'Student']),
            'gender' => $gender,
            'lieunaissance' => json_encode(['fr' => 'City', 'ar' => 'City', 'en' => 'City']),
            'wilaya' => json_encode(['fr' => 'Wilaya', 'ar' => 'Wilaya', 'en' => 'Wilaya']),
            'dayra' => json_encode(['fr' => 'Dayra', 'ar' => 'Dayra', 'en' => 'Dayra']),
            'baladia' => json_encode(['fr' => 'Baladia', 'ar' => 'Baladia', 'en' => 'Baladia']),
            'datenaissance' => '2012-01-01',
            'numtelephone' => 553000000 + random_int(1, 999),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $studentId;
    }

    private function createInscription(int $schoolId, int $gradeId, int $classId, string $status): void
    {
        DB::table('inscriptions')->insert([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classId,
            'inscriptionetat' => 'nouvelleinscription',
            'nomecoleprecedente' => 'old school',
            'dernieresection' => 'section old',
            'moyensannuels' => 12.5,
            'numeronationaletudiant' => 150000000 + random_int(1, 99999),
            'prenom' => json_encode(['fr' => 'Prenom', 'ar' => 'اسم', 'en' => 'Name']),
            'nom' => json_encode(['fr' => 'Nom', 'ar' => 'لقب', 'en' => 'Last']),
            'email' => 'inscription-' . uniqid() . '@example.test',
            'gender' => 1,
            'numtelephone' => 550000000 + random_int(1, 999),
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Address',
            'codepostal' => 16000,
            'residenceactuelle' => 'Residence',
            'etatsante' => 'good',
            'identificationmaladie' => 'none',
            'alfdlprsaldr' => 'notes',
            'autresnotes' => null,
            'prenomwali' => 'Parent',
            'nomwali' => 'Name',
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'numtelephonewali' => 551000000 + random_int(1, 999),
            'emailwali' => 'parent-' . uniqid() . '@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
