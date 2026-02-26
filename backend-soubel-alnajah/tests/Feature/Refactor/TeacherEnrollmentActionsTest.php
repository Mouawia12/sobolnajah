<?php

namespace Tests\Feature\Refactor;

use App\Models\Inscription\Teacher;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherEnrollmentActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_update_updates_teacher_and_related_user(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrapSchoolAdminAndSection();
        $specializationId = $this->createSpecialization('Math');

        $teacherUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);

        $teacher = Teacher::query()->create([
            'user_id' => $teacherUser->id,
            'specialization_id' => $specializationId,
            'name' => ['fr' => 'Old', 'ar' => 'قديم', 'en' => 'Old'],
            'gender' => 1,
            'joining_date' => '2020-09-01',
            'address' => 'Old Address',
        ]);

        DB::table('teacher_section')->insert([
            'teacher_id' => $teacher->id,
            'section_id' => $sectionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->put(route('Teachers.update', ['Teacher' => $teacher->id]), [
            'name_teacherfr' => 'New Name',
            'name_teacherar' => 'اسم جديد',
            'address' => 'New Address',
            'email' => 'teacher.new@example.test',
            'gender' => 0,
            'joining_date' => '2021-10-01',
            'specialization_id' => $specializationId,
        ]);

        $response->assertStatus(302);

        $teacher->refresh();
        $teacherUser->refresh();

        $this->assertSame('New Name', $teacher->getTranslation('name', 'fr'));
        $this->assertSame('اسم جديد', $teacher->getTranslation('name', 'ar'));
        $this->assertSame('New Address', $teacher->address);
        $this->assertSame(0, (int) $teacher->gender);
        $this->assertSame('2021-10-01', (string) $teacher->joining_date);

        $this->assertSame('New Name', $teacherUser->getTranslation('name', 'fr'));
        $this->assertSame('teacher.new@example.test', $teacherUser->email);
        $this->assertSame($schoolId, (int) $teacherUser->school_id);
    }

    public function test_teacher_destroy_deletes_teacher_and_user_when_not_linked_to_sections(): void
    {
        [$admin, $schoolId] = $this->bootstrapSchoolAdminAndSection();
        $specializationId = $this->createSpecialization('Science');

        $teacherUser = User::factory()->create([
            'school_id' => $schoolId,
            'must_change_password' => false,
        ]);

        $teacher = Teacher::query()->create([
            'user_id' => $teacherUser->id,
            'specialization_id' => $specializationId,
            'name' => ['fr' => 'Teacher', 'ar' => 'معلم', 'en' => 'Teacher'],
            'gender' => 1,
            'joining_date' => '2020-09-01',
            'address' => 'Address',
        ]);

        $response = $this->actingAs($admin)->delete(route('Teachers.destroy', ['Teacher' => $teacher->id]));
        $response->assertStatus(302);

        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
        $this->assertDatabaseMissing('users', ['id' => $teacherUser->id]);
    }

    private function bootstrapSchoolAdminAndSection(): array
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId,
            'name_grade' => json_encode(['fr' => 'G1', 'ar' => 'م1', 'en' => 'G1']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C1', 'ar' => 'ق1', 'en' => 'C1']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sectionId = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S1', 'ar' => 'ف1', 'en' => 'S1']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$admin, $schoolId, $sectionId];
    }

    private function createSpecialization(string $name): int
    {
        return DB::table('specializations')->insertGetId([
            'name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
