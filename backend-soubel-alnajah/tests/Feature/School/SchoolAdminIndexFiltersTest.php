<?php

namespace Tests\Feature\School;

use App\Http\Controllers\School\ClassroomController;
use App\Http\Controllers\School\SectionController;
use App\Http\Controllers\School\SchoolController;
use App\Http\Controllers\School\SchoolgradeController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolAdminIndexFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_schoolgrades_index_filters_by_query_and_respects_school_scope(): void
    {
        [$admin, $schoolA] = $this->bootstrapAdmin();
        $schoolB = $this->createSchool('School B');

        DB::table('schoolgrades')->insert([
            [
                'school_id' => $schoolA,
                'name_grade' => json_encode(['fr' => 'Match A', 'ar' => 'مطابقة أ', 'en' => 'Match A']),
                'notes' => json_encode(['fr' => 'Note A', 'ar' => 'ملاحظة أ', 'en' => 'Note A']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $schoolA,
                'name_grade' => json_encode(['fr' => 'General A', 'ar' => 'عام أ', 'en' => 'General A']),
                'notes' => json_encode(['fr' => 'Note B', 'ar' => 'ملاحظة ب', 'en' => 'Note B']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $schoolB,
                'name_grade' => json_encode(['fr' => 'Match B', 'ar' => 'مطابقة ب', 'en' => 'Match B']),
                'notes' => json_encode(['fr' => 'Note C', 'ar' => 'ملاحظة ج', 'en' => 'Note C']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->be($admin);
        $this->app->instance('request', Request::create('/Schoolgrades', 'GET', ['q' => 'Match']));
        $response = app(SchoolgradeController::class)->index();
        $paginator = $response->getData()['Schoolgrade'];

        $this->assertSame(1, $paginator->total());
        $this->assertSame('Match A', $paginator->first()->getTranslation('name_grade', 'en'));
        $this->assertSame($schoolA, $paginator->first()->school_id);
    }

    public function test_classes_index_supports_grade_filter_and_pagination(): void
    {
        [$admin, $schoolId] = $this->bootstrapAdmin();

        $gradeA = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'Grade A', 'ar' => 'مستوى أ', 'en' => 'Grade A']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeB = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'Grade B', 'ar' => 'مستوى ب', 'en' => 'Grade B']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($i = 1; $i <= 22; $i++) {
            DB::table('classrooms')->insert([
                'school_id' => $schoolId,
                'grade_id' => $gradeA,
                'name_class' => json_encode(['fr' => 'Room ' . $i, 'ar' => 'قاعة ' . $i, 'en' => 'Room ' . $i]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('classrooms')->insert([
            'school_id' => $schoolId,
            'grade_id' => $gradeB,
            'name_class' => json_encode(['fr' => 'Filtered Room', 'ar' => 'قاعة مفلترة', 'en' => 'Filtered Room']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->be($admin);
        $this->app->instance('request', Request::create('/Classes', 'GET'));
        $paginatedResponse = app(ClassroomController::class)->index();
        $paginated = $paginatedResponse->getData()['Classroom'];
        $this->assertSame(23, $paginated->total());
        $this->assertCount(20, $paginated->items());

        $this->app->instance('request', Request::create('/Classes', 'GET', ['grade_id' => $gradeB]));
        $filteredResponse = app(ClassroomController::class)->index();
        $filtered = $filteredResponse->getData()['Classroom'];
        $this->assertSame(1, $filtered->total());
        $this->assertSame('Filtered Room', $filtered->first()->getTranslation('name_class', 'en'));
    }

    public function test_sections_index_filters_by_status_and_query_with_school_scope(): void
    {
        [$admin, $schoolA] = $this->bootstrapAdmin();
        $schoolB = $this->createSchool('School B');

        $gradeA = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolA,
            'name_grade' => json_encode(['fr' => 'Grade A', 'ar' => 'مستوى أ', 'en' => 'Grade A']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classA = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolA,
            'grade_id' => $gradeA,
            'name_class' => json_encode(['fr' => 'Class A', 'ar' => 'قسم أ', 'en' => 'Class A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeB = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'Grade B', 'ar' => 'مستوى ب', 'en' => 'Grade B']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classB = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeB,
            'name_class' => json_encode(['fr' => 'Class B', 'ar' => 'قسم ب', 'en' => 'Class B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sections')->insert([
            [
                'school_id' => $schoolA,
                'grade_id' => $gradeA,
                'classroom_id' => $classA,
                'name_section' => json_encode(['fr' => 'Target Section', 'ar' => 'قسم مستهدف', 'en' => 'Target Section']),
                'Status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $schoolA,
                'grade_id' => $gradeA,
                'classroom_id' => $classA,
                'name_section' => json_encode(['fr' => 'Closed Section', 'ar' => 'قسم مغلق', 'en' => 'Closed Section']),
                'Status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'school_id' => $schoolB,
                'grade_id' => $gradeB,
                'classroom_id' => $classB,
                'name_section' => json_encode(['fr' => 'External Section', 'ar' => 'قسم خارجي', 'en' => 'External Section']),
                'Status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->be($admin);
        $this->app->instance('request', Request::create('/Sections', 'GET', ['q' => 'Target', 'status' => '1']));
        $response = app(SectionController::class)->index();
        $grades = $response->getData()['Schoolgrade'];
        $sectionFromGrade = $grades->first()->sections->first();

        $this->assertSame(1, $grades->total());
        $this->assertSame('Target Section', $sectionFromGrade->getTranslation('name_section', 'en'));
        $this->assertSame($schoolA, $sectionFromGrade->school_id);
    }

    public function test_schools_index_supports_search_and_pagination_with_school_scope(): void
    {
        [$admin, $schoolA] = $this->bootstrapAdmin();
        $schoolB = $this->createSchool('Blocked School');

        for ($i = 1; $i <= 25; $i++) {
            DB::table('schools')->insert([
                'name_school' => json_encode(['fr' => 'Extra ' . $i, 'ar' => 'إضافي ' . $i, 'en' => 'Extra ' . $i]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->be($admin);
        $this->app->instance('request', Request::create('/Schools', 'GET', ['q' => 'Blocked']));
        $filteredResponse = app(SchoolController::class)->index();
        $filtered = $filteredResponse->getData()['School'];
        $this->assertSame(0, $filtered->total());

        $admin->update(['school_id' => null]);
        $this->be($admin);
        $this->app->instance('request', Request::create('/Schools', 'GET', ['q' => 'Extra']));
        $globalResponse = app(SchoolController::class)->index();
        $global = $globalResponse->getData()['School'];

        $this->assertTrue($global->total() >= 20);
        $this->assertCount(20, $global->items());
        $this->assertStringContainsString('Extra', $global->items()[0]->getTranslation('name_school', 'en'));
    }

    private function bootstrapAdmin(): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = $this->createSchool('School A');
        $admin->update(['school_id' => $schoolId]);

        return [$admin, $schoolId];
    }

    private function createSchool(string $name): int
    {
        return DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => $name, 'ar' => $name, 'en' => $name]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
