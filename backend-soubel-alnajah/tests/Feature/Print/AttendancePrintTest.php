<?php

namespace Tests\Feature\Print;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class AttendancePrintTest extends TestCase
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

    public function test_student_attendance_report_page_renders_with_letterhead(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrap('A');
        $studentId = $this->createStudent($schoolId, $sectionId, 'علي', 'بن');
        $this->insertAbsence($studentId, now()->toDateString());

        $response = $this->actingAs($admin)->get(route('absences.student.report', $studentId));

        $response->assertStatus(200);
        $response->assertSee(trans('print.student_attendance_report'));
        $response->assertSee('علي');
    }

    public function test_student_attendance_report_pdf_downloads(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrap('A');
        $studentId = $this->createStudent($schoolId, $sectionId, 'سارة', 'محمد');
        $this->insertAbsence($studentId, now()->toDateString());

        $response = $this->actingAs($admin)->get(route('absences.student.report.pdf', $studentId));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', strtolower((string) $response->headers->get('content-type')));
    }

    public function test_student_report_is_school_scoped(): void
    {
        [$admin] = $this->bootstrap('A');
        [, $schoolB, $sectionB] = $this->bootstrap('B');
        $foreignStudent = $this->createStudent($schoolB, $sectionB, 'خارجي', 'أجنبي');

        $response = $this->actingAs($admin)->get(route('absences.student.report', $foreignStudent));

        $response->assertStatus(404);
    }

    public function test_staff_attendance_report_print_renders(): void
    {
        [$admin] = $this->bootstrap('A');

        $response = $this->actingAs($admin)->get(route('staff-attendance.report.print'));

        $response->assertStatus(200);
        $response->assertSee(trans('print.staff_attendance_report'));
    }

    private function bootstrap(string $suffix): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G', 'ar' => 'م', 'en' => 'G']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C', 'ar' => 'ق', 'en' => 'C']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S', 'ar' => 'ف', 'en' => 'S']),
            'Status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$admin, $schoolId, $sectionId];
    }

    private function createStudent(int $schoolId, int $sectionId, string $prenom, string $nom): int
    {
        $userId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => $prenom, 'ar' => $prenom, 'en' => $prenom]),
            'email' => 'student-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'), 'school_id' => $schoolId,
            'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $parentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'email' => 'parent-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'), 'school_id' => $schoolId,
            'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'و', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550000000 + random_int(1, 999),
            'user_id' => $parentUserId, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('studentinfos')->insertGetId([
            'user_id' => $userId, 'section_id' => $sectionId, 'parent_id' => $parentId, 'gender' => 1,
            'prenom' => json_encode(['fr' => $prenom, 'ar' => $prenom, 'en' => $prenom]),
            'nom' => json_encode(['fr' => $nom, 'ar' => $nom, 'en' => $nom]),
            'lieunaissance' => 'City', 'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B',
            'datenaissance' => '2012-01-01', 'numtelephone' => 550000000 + random_int(1, 999),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function insertAbsence(int $studentId, string $date): void
    {
        DB::table('absences')->insert([
            'student_id' => $studentId, 'date' => $date,
            'hour_1' => 1, 'hour_2' => 0, 'hour_3' => 2, 'hour_4' => 1, 'hour_5' => 1,
            'hour_6' => 1, 'hour_7' => 1, 'hour_8' => 1, 'hour_9' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
