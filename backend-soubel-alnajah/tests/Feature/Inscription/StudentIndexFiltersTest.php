<?php

namespace Tests\Feature\Inscription;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class StudentIndexFiltersTest extends TestCase
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
    }

    public function test_students_index_is_school_scoped_and_paginated(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolA, $sectionA1] = $this->createSchoolHierarchy('A');
        [$schoolB, $sectionB1] = $this->createSchoolHierarchy('B');

        $admin->update(['school_id' => $schoolA]);

        for ($i = 1; $i <= 25; $i++) {
            $this->createStudent($schoolA, $sectionA1, 'A' . $i, 551000000 + $i);
        }

        $this->createStudent($schoolB, $sectionB1, 'B1', 552000001);

        $response = $this->actingAs($admin)->get(route('Students.index'));
        $response->assertStatus(200);
        $response->assertViewHas('StudentInfo', function ($paginator) {
            return $paginator->count() === 20 && $paginator->total() === 25;
        });
    }

    public function test_students_index_can_filter_by_section(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolA, $sectionA1, $sectionA2] = $this->createSchoolHierarchy('A', true);
        $admin->update(['school_id' => $schoolA]);

        $this->createStudent($schoolA, $sectionA1, 'X1', 553000001);
        $this->createStudent($schoolA, $sectionA1, 'X2', 553000002);
        $this->createStudent($schoolA, $sectionA2, 'Y1', 553000003);

        $response = $this->actingAs($admin)->get(route('Students.index', ['section_id' => $sectionA1]));
        $response->assertStatus(200);
        $response->assertViewHas('StudentInfo', function ($paginator) {
            return $paginator->total() === 2;
        });
    }

    private function createSchoolHierarchy(string $suffix, bool $withSecondSection = false): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G' . $suffix, 'ar' => 'م' . $suffix, 'en' => 'G' . $suffix]),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C' . $suffix, 'ar' => 'ق' . $suffix, 'en' => 'C' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $section1 = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1' . $suffix, 'ar' => 'ف1' . $suffix, 'en' => 'S1' . $suffix]),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$withSecondSection) {
            return [$schoolId, $section1];
        }

        $section2 = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S2' . $suffix, 'ar' => 'ف2' . $suffix, 'en' => 'S2' . $suffix]),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $section1, $section2];
    }

    private function createStudent(int $schoolId, int $sectionId, string $suffix, int $phone): void
    {
        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Parent ' . $suffix, 'ar' => 'ولي ' . $suffix, 'en' => 'Parent ' . $suffix]),
            'email' => 'parent-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'Parent', 'ar' => 'ولي', 'en' => 'Parent']),
            'nomwali' => json_encode(['fr' => $suffix, 'ar' => $suffix, 'en' => $suffix]),
            'relationetudiant' => json_encode(['fr' => 'Father', 'ar' => 'الأب', 'en' => 'Father']),
            'adressewali' => json_encode(['fr' => 'Address', 'ar' => 'عنوان', 'en' => 'Address']),
            'wilayawali' => json_encode(['fr' => 'Wilaya', 'ar' => 'ولاية', 'en' => 'Wilaya']),
            'dayrawali' => json_encode(['fr' => 'Dayra', 'ar' => 'دائرة', 'en' => 'Dayra']),
            'baladiawali' => json_encode(['fr' => 'Baladia', 'ar' => 'بلدية', 'en' => 'Baladia']),
            'numtelephonewali' => $phone + 100,
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Student ' . $suffix, 'ar' => 'تلميذ ' . $suffix, 'en' => 'Student ' . $suffix]),
            'email' => 'student-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('studentinfos')->insert([
            'user_id' => $studentUserId,
            'section_id' => $sectionId,
            'parent_id' => $parentId,
            'prenom' => json_encode(['fr' => 'Prenom ' . $suffix, 'ar' => 'اسم ' . $suffix, 'en' => 'Name ' . $suffix]),
            'nom' => json_encode(['fr' => 'Nom ' . $suffix, 'ar' => 'لقب ' . $suffix, 'en' => 'Last ' . $suffix]),
            'gender' => 1,
            'lieunaissance' => json_encode(['fr' => 'City', 'ar' => 'مدينة', 'en' => 'City']),
            'wilaya' => json_encode(['fr' => 'Wilaya', 'ar' => 'ولاية', 'en' => 'Wilaya']),
            'dayra' => json_encode(['fr' => 'Dayra', 'ar' => 'دائرة', 'en' => 'Dayra']),
            'baladia' => json_encode(['fr' => 'Baladia', 'ar' => 'بلدية', 'en' => 'Baladia']),
            'datenaissance' => '2012-01-01',
            'numtelephone' => $phone,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
