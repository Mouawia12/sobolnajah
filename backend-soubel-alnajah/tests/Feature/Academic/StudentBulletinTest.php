<?php

namespace Tests\Feature\Academic;

use App\Models\Academic\Assessment;
use App\Models\Academic\AssessmentMark;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class StudentBulletinTest extends TestCase
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

        foreach (['admin', 'student', 'guardian'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_student_sees_own_bulletin_with_average(): void
    {
        [$ctx] = [$this->bootstrap()];
        $studentUser = User::query()->find($ctx['student_user_id']);
        $studentUser->attachRole('student');

        $response = $this->actingAs($studentUser)->get(route('reports.bulletin', $ctx['student_id']));
        $response->assertStatus(200);
        $response->assertSee(trans('academic.results_sheet'));
        // معدّل المادة: 15/20 => 15.00 يظهر
        $response->assertSee('15.00');
    }

    public function test_guardian_sees_child_bulletin(): void
    {
        $ctx = $this->bootstrap();
        $guardianUser = User::query()->find($ctx['guardian_user_id']);
        $guardianUser->attachRole('guardian');

        $response = $this->actingAs($guardianUser)->get(route('reports.bulletin', $ctx['student_id']));
        $response->assertStatus(200);
        $response->assertSee(trans('academic.general_average'));
    }

    public function test_bulletin_pdf_downloads(): void
    {
        $ctx = $this->bootstrap();
        $studentUser = User::query()->find($ctx['student_user_id']);
        $studentUser->attachRole('student');

        $response = $this->actingAs($studentUser)->get(route('reports.bulletin', ['student' => $ctx['student_id'], 'format' => 'pdf']));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', strtolower((string) $response->headers->get('content-type')));
    }

    public function test_student_cannot_see_another_students_bulletin(): void
    {
        $ctx = $this->bootstrap();
        // تلميذ آخر (مستخدم منفصل) يحاول رؤية كشف الأول
        $intruder = User::factory()->create(['must_change_password' => false, 'school_id' => $ctx['school_id']]);
        $intruder->attachRole('student');

        $response = $this->actingAs($intruder)->get(route('reports.bulletin', $ctx['student_id']));
        $response->assertStatus(403);
    }

    private function bootstrap(): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'مدرسة', 'en' => 'S']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
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
        $specId = DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $studentUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'S', 'ar' => 'تلميذ', 'en' => 'S']),
            'email' => 'st-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $guardianUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'P', 'ar' => 'ولي', 'en' => 'P']),
            'email' => 'gu-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $parentId = DB::table('my_parents')->insertGetId([
            'prenomwali' => json_encode(['fr' => 'P', 'ar' => 'ولي', 'en' => 'P']),
            'nomwali' => json_encode(['fr' => 'L', 'ar' => 'ل', 'en' => 'L']),
            'relationetudiant' => 'father', 'adressewali' => 'A', 'wilayawali' => 'W',
            'dayrawali' => 'D', 'baladiawali' => 'B', 'numtelephonewali' => 550000001,
            'user_id' => $guardianUserId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $studentId = DB::table('studentinfos')->insertGetId([
            'user_id' => $studentUserId, 'section_id' => $sectionId, 'parent_id' => $parentId, 'gender' => 1,
            'prenom' => json_encode(['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali']),
            'nom' => json_encode(['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben']),
            'lieunaissance' => 'C', 'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B',
            'datenaissance' => '2012-01-01', 'numtelephone' => 550000002,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $assessment = Assessment::query()->create([
            'school_id' => $schoolId, 'section_id' => $sectionId, 'specialization_id' => $specId,
            'title' => 'الفرض الأول', 'type' => 'devoir', 'max_mark' => 20, 'coefficient' => 1,
        ]);
        AssessmentMark::query()->create([
            'assessment_id' => $assessment->id, 'student_id' => $studentId, 'mark' => 15, 'is_absent' => false,
        ]);

        return [
            'school_id' => $schoolId, 'section_id' => $sectionId,
            'student_id' => $studentId, 'student_user_id' => $studentUserId,
            'guardian_user_id' => $guardianUserId,
        ];
    }
}
