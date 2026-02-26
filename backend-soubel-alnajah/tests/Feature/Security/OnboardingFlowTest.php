<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\School\Section;
use App\Models\Specialization\Specialization;
use App\Models\User;
use App\Services\StudentEnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_enrollment_dispatches_password_setup_links(): void
    {
        $this->seedRequiredRoles();
        $schoolId = $this->createSchoolHierarchy();

        $section = Section::query()->where('school_id', $schoolId)->firstOrFail();

        /** @var StudentEnrollmentService $service */
        $service = app(StudentEnrollmentService::class);

        $service->createStudent(
            [
                'first_name' => ['fr' => 'Ali', 'ar' => 'علي', 'en' => 'Ali'],
                'last_name' => ['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben'],
                'email' => 'student@example.test',
                'gender' => 1,
                'phone' => '0555000001',
                'birth_date' => '2012-01-01',
                'birth_place' => 'City',
                'wilaya' => 'Wilaya',
                'dayra' => 'Dayra',
                'baladia' => 'Baladia',
            ],
            [
                'first_name' => ['fr' => 'Fatima', 'ar' => 'فاطمة', 'en' => 'Fatima'],
                'last_name' => ['fr' => 'Ben', 'ar' => 'بن', 'en' => 'Ben'],
                'relation' => 'mother',
                'address' => 'Address',
                'wilaya' => 'Wilaya',
                'dayra' => 'Dayra',
                'baladia' => 'Baladia',
                'phone' => '0555000002',
                'email' => 'guardian@example.test',
            ],
            $section
        );

        $this->assertDatabaseHas('users', [
            'email' => 'student@example.test',
            'must_change_password' => 1,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'guardian@example.test',
            'must_change_password' => 1,
        ]);

        $this->assertDatabaseHas('password_resets', [
            'email' => 'student@example.test',
        ]);

        $this->assertDatabaseHas('password_resets', [
            'email' => 'guardian@example.test',
        ]);
    }

    public function test_teacher_creation_dispatches_password_setup_link(): void
    {
        $this->seedRequiredRoles();
        $schoolId = $this->createSchoolHierarchy();

        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);
        $admin->attachRole('admin');

        $specialization = Specialization::create([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']),
        ]);

        $response = $this->actingAs($admin)->post(route('Teachers.store'), [
            'name_teacherfr' => 'Teacher',
            'name_teacherar' => 'أستاذ',
            'address' => 'Address',
            'email' => 'teacher@example.test',
            'gender' => 1,
            'specialization_id' => $specialization->id,
            'joining_date' => '2025-01-01',
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.test',
            'must_change_password' => 1,
        ]);

        $this->assertDatabaseHas('password_resets', [
            'email' => 'teacher@example.test',
        ]);
    }

    private function seedRequiredRoles(): void
    {
        foreach (['admin', 'teacher', 'student', 'guardian'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    private function createSchoolHierarchy(): int
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School', 'ar' => 'مدرسة', 'en' => 'School']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'Grade 1', 'ar' => 'المستوى 1', 'en' => 'Grade 1']),
            'notes' => json_encode(['fr' => 'Note', 'ar' => 'ملاحظة', 'en' => 'Note']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'Class A', 'ar' => 'قسم أ', 'en' => 'Class A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'Section 1', 'ar' => 'الفوج 1', 'en' => 'Section 1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $schoolId;
    }
}
