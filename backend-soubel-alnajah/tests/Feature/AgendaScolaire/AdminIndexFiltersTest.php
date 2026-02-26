<?php

namespace Tests\Feature\AgendaScolaire;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class AdminIndexFiltersTest extends TestCase
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

    public function test_absence_index_is_school_scoped_and_paginated(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolA, $sectionA] = $this->createSchoolSection('A');
        [$schoolB, $sectionB] = $this->createSchoolSection('B');
        $admin->update(['school_id' => $schoolA]);

        for ($i = 1; $i <= 25; $i++) {
            $studentId = $this->createStudent($schoolA, $sectionA, 'A' . $i, 560000000 + $i);
            DB::table('absences')->insert([
                'student_id' => $studentId,
                'date' => '2026-02-' . str_pad((string) (($i % 9) + 1), 2, '0', STR_PAD_LEFT),
                'hour_1' => true,
                'hour_2' => false,
                'hour_3' => true,
                'hour_4' => false,
                'hour_5' => true,
                'hour_6' => false,
                'hour_7' => true,
                'hour_8' => false,
                'hour_9' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $studentB = $this->createStudent($schoolB, $sectionB, 'B1', 561000001);
        DB::table('absences')->insert([
            'student_id' => $studentB,
            'date' => '2026-02-03',
            'hour_1' => true,
            'hour_2' => true,
            'hour_3' => true,
            'hour_4' => true,
            'hour_5' => true,
            'hour_6' => true,
            'hour_7' => true,
            'hour_8' => true,
            'hour_9' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('Absences.index'));
        $response->assertStatus(200);
        $response->assertViewHas('Absence', function ($paginator) {
            return $paginator->count() === 20 && $paginator->total() === 25;
        });
    }

    public function test_publications_index_is_school_scoped_paginated_and_filterable(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolA, $gradeA, $agendaA] = $this->createPublicationReferences('A');
        [$schoolB, $gradeB, $agendaB] = $this->createPublicationReferences('B');
        $admin->update(['school_id' => $schoolA]);

        for ($i = 1; $i <= 22; $i++) {
            DB::table('publications')->insert([
                'school_id' => $schoolA,
                'grade_id' => $gradeA,
                'agenda_id' => $agendaA,
                'title' => json_encode(['fr' => 'Title A' . $i, 'ar' => 'عنوان A' . $i, 'en' => 'Title A' . $i]),
                'body' => json_encode(['fr' => 'Body A' . $i, 'ar' => 'محتوى A' . $i, 'en' => 'Body A' . $i]),
                'like' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('publications')->insert([
            'school_id' => $schoolA,
            'grade_id' => $gradeA,
            'agenda_id' => $agendaA,
            'title' => json_encode(['fr' => 'Special Filter', 'ar' => 'فلتر خاص', 'en' => 'Special Filter']),
            'body' => json_encode(['fr' => 'Body', 'ar' => 'محتوى', 'en' => 'Body']),
            'like' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('publications')->insert([
            'school_id' => $schoolB,
            'grade_id' => $gradeB,
            'agenda_id' => $agendaB,
            'title' => json_encode(['fr' => 'Other School', 'ar' => 'مدرسة أخرى', 'en' => 'Other School']),
            'body' => json_encode(['fr' => 'Body', 'ar' => 'محتوى', 'en' => 'Body']),
            'like' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('Publications.index'));
        $response->assertStatus(200);
        $response->assertViewHas('Publications', function ($paginator) {
            return $paginator->count() === 20 && $paginator->total() === 23;
        });

        $filtered = $this->actingAs($admin)->get(route('Publications.index', ['q' => 'Special Filter']));
        $filtered->assertStatus(200);
        $filtered->assertViewHas('Publications', function ($paginator) {
            return $paginator->total() === 1;
        });
    }

    public function test_public_publications_page_does_not_load_admin_datasets(): void
    {
        [$schoolA, $gradeA, $agendaA] = $this->createPublicationReferences('A');

        for ($i = 1; $i <= 15; $i++) {
            DB::table('publications')->insert([
                'school_id' => $schoolA,
                'grade_id' => $gradeA,
                'agenda_id' => $agendaA,
                'title' => json_encode(['fr' => 'Public ' . $i, 'ar' => 'عام ' . $i, 'en' => 'Public ' . $i]),
                'body' => json_encode(['fr' => 'Body', 'ar' => 'محتوى', 'en' => 'Body']),
                'like' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->get(route('Publications.index'));
        $response->assertStatus(200);
        $response->assertViewIs('front-end.publications');
        $response->assertViewMissing('Publications');
        $response->assertViewMissing('Grade');
        $response->assertViewMissing('Agenda');
        $response->assertViewMissing('School');
    }

    private function createSchoolSection(string $suffix): array
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

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'ف' . $suffix, 'en' => 'S' . $suffix]),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $sectionId];
    }

    private function createPublicationReferences(string $suffix): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('grades')->insertGetId([
            'name_grades' => json_encode(['fr' => 'Grade ' . $suffix, 'ar' => 'طور ' . $suffix, 'en' => 'Grade ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agendaId = DB::table('agenda')->insertGetId([
            'name_agenda' => json_encode(['fr' => 'Agenda ' . $suffix, 'ar' => 'أجندة ' . $suffix, 'en' => 'Agenda ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $gradeId, $agendaId];
    }

    private function createStudent(int $schoolId, int $sectionId, string $suffix, int $phone): int
    {
        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Parent ' . $suffix, 'ar' => 'ولي ' . $suffix, 'en' => 'Parent ' . $suffix]),
            'email' => 'parent-absence-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
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
            'numtelephonewali' => $phone + 200,
            'user_id' => $parentUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Student ' . $suffix, 'ar' => 'تلميذ ' . $suffix, 'en' => 'Student ' . $suffix]),
            'email' => 'student-absence-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('studentinfos')->insertGetId([
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
