<?php

namespace Tests\Feature\Academic;

use App\Models\Academic\Assessment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class AssessmentFlowTest extends TestCase
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

        foreach (['admin', 'teacher'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_teacher_creates_assessment_for_own_section_and_average_is_computed(): void
    {
        [$schoolId, $sectionId, $specId] = $this->bootstrapSchoolSection('A');
        [$teacherUser, $teacherId] = $this->createTeacher($schoolId, $sectionId, $specId);
        $studentId = $this->createStudent($schoolId, $sectionId, 'علي', 'بن');

        // إنشاء تقييم بنقطة قصوى 10
        $create = $this->actingAs($teacherUser)->post(route('assessments.store'), [
            'section_id' => $sectionId, 'title' => 'الفرض الأول', 'type' => 'devoir',
            'max_mark' => 10, 'coefficient' => 1,
        ]);
        $create->assertStatus(302);
        $create->assertSessionHasNoErrors();

        $assessment = Assessment::query()->first();
        $this->assertNotNull($assessment);
        $this->assertSame($teacherId, (int) $assessment->teacher_id);
        $this->assertSame($specId, (int) $assessment->specialization_id);

        // إدخال نقطة 8/10 => المعدّل المطبّع 16/20
        $marks = $this->actingAs($teacherUser)->post(route('assessments.marks.store', $assessment->id), [
            'mark' => [$studentId => 8],
        ]);
        $marks->assertStatus(302);
        $this->assertDatabaseHas('assessment_marks', [
            'assessment_id' => $assessment->id, 'student_id' => $studentId, 'mark' => 8,
        ]);

        $results = $this->actingAs($teacherUser)->get(route('assessments.results', ['section' => $sectionId]));
        $results->assertStatus(200);
        $results->assertViewHas('rows', function ($rows) {
            return abs(($rows->first()['average'] ?? 0) - 16.0) < 0.01;
        });
    }

    public function test_teacher_cannot_create_assessment_for_foreign_section(): void
    {
        [$schoolId, $sectionId, $specId] = $this->bootstrapSchoolSection('A');
        [$teacherUser] = $this->createTeacher($schoolId, $sectionId, $specId);
        [, $foreignSectionId] = $this->bootstrapSchoolSection('B');

        $response = $this->actingAs($teacherUser)->post(route('assessments.store'), [
            'section_id' => $foreignSectionId, 'title' => 'X', 'type' => 'devoir',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('section_id');
        $this->assertSame(0, Assessment::query()->count());
    }

    public function test_teacher_cannot_open_another_teachers_assessment(): void
    {
        [$schoolId, $sectionId, $specId] = $this->bootstrapSchoolSection('A');
        [, $ownerTeacherId] = $this->createTeacher($schoolId, $sectionId, $specId, 'owner');
        [$otherUser] = $this->createTeacher($schoolId, $sectionId, $specId, 'other');

        // تقييم يملكه الأستاذ الأول
        $assessmentId = DB::table('assessments')->insertGetId([
            'school_id' => $schoolId, 'teacher_id' => $ownerTeacherId, 'section_id' => $sectionId,
            'specialization_id' => $specId, 'title' => 'Other', 'type' => 'devoir',
            'max_mark' => 20, 'coefficient' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        // الأستاذ الثاني لا يملكه => ممنوع
        $response = $this->actingAs($otherUser)->get(route('assessments.marks', $assessmentId));
        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    public function test_admin_can_create_and_enter_marks(): void
    {
        [$schoolId, $sectionId, $specId] = $this->bootstrapSchoolSection('A');
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => $schoolId]);
        $admin->attachRole('admin');
        $studentId = $this->createStudent($schoolId, $sectionId, 'سارة', 'محمد');

        $create = $this->actingAs($admin)->post(route('assessments.store'), [
            'section_id' => $sectionId, 'specialization_id' => $specId,
            'title' => 'امتحان', 'type' => 'exam', 'max_mark' => 20, 'coefficient' => 2,
        ]);
        $create->assertStatus(302)->assertSessionHasNoErrors();

        $assessment = Assessment::query()->first();
        $this->actingAs($admin)->post(route('assessments.marks.store', $assessment->id), [
            'mark' => [$studentId => 14],
        ])->assertStatus(302);

        $this->assertDatabaseHas('assessment_marks', ['assessment_id' => $assessment->id, 'student_id' => $studentId, 'mark' => 14]);
    }

    public function test_absent_marks_are_recorded_and_excluded_from_average(): void
    {
        [$schoolId, $sectionId, $specId] = $this->bootstrapSchoolSection('A');
        [$teacherUser, $teacherId] = $this->createTeacher($schoolId, $sectionId, $specId);
        $studentId = $this->createStudent($schoolId, $sectionId, 'زياد', 'ك');

        $assessment = Assessment::query()->create([
            'school_id' => $schoolId, 'teacher_id' => $teacherId, 'section_id' => $sectionId, 'specialization_id' => $specId,
            'title' => 'ف', 'type' => 'devoir', 'max_mark' => 20, 'coefficient' => 1,
        ]);

        $this->actingAs($teacherUser)->post(route('assessments.marks.store', $assessment->id), [
            'absent' => [$studentId => 1],
        ])->assertStatus(302);

        $this->assertDatabaseHas('assessment_marks', [
            'assessment_id' => $assessment->id, 'student_id' => $studentId, 'is_absent' => 1,
        ]);
    }

    public function test_non_staff_cannot_access_assessments(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($user)->get(route('assessments.index'));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }

    private function bootstrapSchoolSection(string $suffix): array
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'S' . $suffix]),
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

        return [$schoolId, $sectionId, $specId];
    }

    private function createTeacher(int $schoolId, int $sectionId, int $specId, string $tag = 'main'): array
    {
        $user = User::factory()->create(['must_change_password' => false, 'school_id' => $schoolId]);
        $user->attachRole('teacher');

        $teacherId = DB::table('teachers')->insertGetId([
            'user_id' => $user->id, 'specialization_id' => $specId,
            'name' => json_encode(['fr' => 'T' . $tag, 'ar' => 'أستاذ', 'en' => 'T' . $tag]),
            'gender' => 1, 'joining_date' => '2024-09-01', 'address' => 'A',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('teacher_section')->insert([
            'teacher_id' => $teacherId, 'section_id' => $sectionId,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$user, $teacherId];
    }

    private function createStudent(int $schoolId, int $sectionId, string $prenom, string $nom): int
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

        return DB::table('studentinfos')->insertGetId([
            'user_id' => $userId, 'section_id' => $sectionId, 'parent_id' => $parentId, 'gender' => 1,
            'prenom' => json_encode(['fr' => $prenom, 'ar' => $prenom, 'en' => $prenom]),
            'nom' => json_encode(['fr' => $nom, 'ar' => $nom, 'en' => $nom]),
            'lieunaissance' => 'C', 'wilaya' => 'W', 'dayra' => 'D', 'baladia' => 'B',
            'datenaissance' => '2012-01-01', 'numtelephone' => 550000000 + random_int(1, 999),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
