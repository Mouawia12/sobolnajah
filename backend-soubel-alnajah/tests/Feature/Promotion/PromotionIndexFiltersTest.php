<?php

namespace Tests\Feature\Promotion;

use App\Http\Controllers\Promotion\PromotionController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PromotionIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotions_index_filters_by_student_name_with_school_scope(): void
    {
        [$admin, $schoolA] = $this->bootstrapAdminWithSchool();
        [$schoolB, $studentB, $gradeB, $classroomB, $sectionB] = $this->createStudentInSchool('B');
        [, $studentA, $gradeA, $classroomA, $sectionA] = $this->createStudentInSchool('A', $schoolA);

        DB::table('promotions')->insert([
            [
                'student_id' => $studentA,
                'from_school' => $schoolA,
                'from_grade' => $gradeA,
                'from_Classroom' => $classroomA,
                'from_section' => $sectionA,
                'to_school' => $schoolA,
                'to_grade' => $gradeA,
                'to_Classroom' => $classroomA,
                'to_section' => $sectionA,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_id' => $studentB,
                'from_school' => $schoolB,
                'from_grade' => $gradeB,
                'from_Classroom' => $classroomB,
                'from_section' => $sectionB,
                'to_school' => $schoolB,
                'to_grade' => $gradeB,
                'to_Classroom' => $classroomB,
                'to_section' => $sectionB,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->be($admin);
        $this->app->instance('request', Request::create('/Promotions', 'GET', ['q' => 'A']));
        $response = app(PromotionController::class)->index();
        $promotions = $response->getData()['promotions'];

        $this->assertSame(1, $promotions->total());
        $this->assertSame($schoolA, $promotions->first()->from_school);
    }

    private function bootstrapAdminWithSchool(): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'School A', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->update(['school_id' => $schoolId]);

        return [$admin, $schoolId];
    }

    private function createStudentInSchool(string $suffix, ?int $schoolId = null): array
    {
        $schoolId = $schoolId ?: DB::table('schools')->insertGetId([
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

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C' . $suffix, 'ar' => 'C' . $suffix, 'en' => 'C' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'S' . $suffix, 'en' => 'S' . $suffix]),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => $suffix . ' Student', 'ar' => $suffix . ' Student', 'en' => $suffix . ' Student']),
            'email' => 'student-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => $suffix . ' Parent', 'ar' => $suffix . ' Parent', 'en' => $suffix . ' Parent']),
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
            'numtelephonewali' => 550001500 + random_int(1, 99),
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $userId,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'prenom' => json_encode(['fr' => $suffix, 'ar' => $suffix, 'en' => $suffix]),
            'nom' => json_encode(['fr' => 'Student', 'ar' => 'Student', 'en' => 'Student']),
            'gender' => 1,
            'lieunaissance' => json_encode(['fr' => 'City', 'ar' => 'City', 'en' => 'City']),
            'wilaya' => json_encode(['fr' => 'Wilaya', 'ar' => 'Wilaya', 'en' => 'Wilaya']),
            'dayra' => json_encode(['fr' => 'Dayra', 'ar' => 'Dayra', 'en' => 'Dayra']),
            'baladia' => json_encode(['fr' => 'Baladia', 'ar' => 'Baladia', 'en' => 'Baladia']),
            'datenaissance' => '2012-01-01',
            'numtelephone' => 550001100 + random_int(1, 99),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $studentId, $gradeId, $classroomId, $sectionId];
    }
}
