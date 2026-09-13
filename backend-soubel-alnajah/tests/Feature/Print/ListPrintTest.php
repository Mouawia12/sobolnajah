<?php

namespace Tests\Feature\Print;

use App\Models\HR\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class ListPrintTest extends TestCase
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

        Role::firstOrCreate(['name' => 'admin']);
    }

    public function test_students_list_print_renders_scoped(): void
    {
        [$admin, $schoolA, $sectionA] = $this->bootstrap('A');
        $this->createStudent($schoolA, $sectionA, 'داخلي', 'أ');
        [, $schoolB, $sectionB] = $this->bootstrap('B');
        $this->createStudent($schoolB, $sectionB, 'خارجي', 'ب');

        $response = $this->actingAs($admin)->get(route('students.print'));

        $response->assertStatus(200);
        $response->assertSee(trans('print.students_list'));
        $response->assertSee('داخلي');
        $response->assertDontSee('خارجي');
    }

    public function test_students_list_pdf_downloads(): void
    {
        [$admin, $schoolA, $sectionA] = $this->bootstrap('A');
        $this->createStudent($schoolA, $sectionA, 'تلميذ', 'ب');

        $response = $this->actingAs($admin)->get(route('students.print', ['format' => 'pdf']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', strtolower((string) $response->headers->get('content-type')));
    }

    public function test_employees_list_print_renders(): void
    {
        [$admin, $schoolA] = $this->bootstrap('A');
        Employee::query()->create(['school_id' => $schoolA, 'name' => 'موظف طباعة', 'is_active' => true]);

        $response = $this->actingAs($admin)->get(route('employees.print'));

        $response->assertStatus(200);
        $response->assertSee(trans('print.employees_list'));
        $response->assertSee('موظف طباعة');
    }

    public function test_teachers_list_print_renders(): void
    {
        [$admin, $schoolA] = $this->bootstrap('A');
        $this->createTeacher($schoolA, 'أستاذ طباعة');

        $response = $this->actingAs($admin)->get(route('teachers.print'));

        $response->assertStatus(200);
        $response->assertSee(trans('print.teachers_list'));
        $response->assertSee('أستاذ طباعة');
    }

    private function bootstrap(string $suffix): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'S' . $suffix]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

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

        return [$admin, $schoolId, $sectionId];
    }

    private function createStudent(int $schoolId, int $sectionId, string $prenom, string $nom): void
    {
        $userId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => $prenom, 'ar' => $prenom, 'en' => $prenom]),
            'email' => 'st-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'email' => 'pa-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550000000 + random_int(1, 999),
            'user_id' => $parentUserId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('studentinfos')->insert([
            'user_id' => $userId, 'section_id' => $sectionId, 'parent_id' => $parentId, 'gender' => 1,
            'prenom' => json_encode(['fr' => $prenom, 'ar' => $prenom, 'en' => $prenom]),
            'nom' => json_encode(['fr' => $nom, 'ar' => $nom, 'en' => $nom]),
            'lieunaissance' => 'C', 'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B',
            'datenaissance' => '2012-01-01', 'numtelephone' => 550000000 + random_int(1, 999),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function createTeacher(int $schoolId, string $name): void
    {
        $specId = DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => 'Sp', 'ar' => 'تخصص', 'en' => 'Sp']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $userId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => $name, 'ar' => $name, 'en' => $name]),
            'email' => 'te-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('teachers')->insert([
            'user_id' => $userId, 'specialization_id' => $specId,
            'name' => json_encode(['fr' => $name, 'ar' => $name, 'en' => $name]),
            'gender' => 1, 'joining_date' => '2024-09-01', 'address' => 'A',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
