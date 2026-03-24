<?php

namespace Tests\Feature\Timetable;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class TeacherScheduleFlowTest extends TestCase
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

    public function test_admin_without_default_school_can_create_teacher_schedule_for_selected_school(): void
    {
        $admin = $this->createAdminWithoutSchool();
        [$schoolId, $teacherId] = $this->bootstrapTeacherForSchool(false);

        $response = $this->actingAs($admin)->post(route('teacher-schedules.store'), [
            'school_id' => $schoolId,
            'teacher_id' => $teacherId,
            'academic_year' => '2026/2027',
            'title' => 'جدول الأستاذ',
            'branch_name' => 'الفرع الرئيسي',
            'status' => 'published',
            'visibility' => 'authenticated',
            'approved_at' => '2026-03-01',
            'signature_text' => 'الإدارة',
            'slots' => [
                [
                    'slot_index' => 1,
                    'label' => '08:00-09:00',
                    'starts_at' => '08:00',
                    'ends_at' => '09:00',
                ],
            ],
            'entries' => [
                1 => [
                    1 => [
                        'subject_name' => 'رياضيات',
                        'class_name' => 'السنة الأولى',
                        'room_name' => 'A1',
                        'note' => 'مخبر',
                    ],
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('teacher_schedules', [
            'school_id' => $schoolId,
            'teacher_id' => $teacherId,
            'academic_year' => '2026/2027',
            'status' => 'published',
            'visibility' => 'authenticated',
        ]);
        $this->assertDatabaseHas('teacher_schedule_entries', [
            'day_of_week' => 1,
            'slot_index' => 1,
            'subject_name' => 'رياضيات',
        ]);
    }

    public function test_admin_can_create_teacher_schedule_for_unassigned_legacy_teacher_when_school_is_selected(): void
    {
        $admin = $this->createAdminWithoutSchool();
        [$schoolId, $teacherId] = $this->bootstrapTeacherForSchool(false, false);

        $response = $this->actingAs($admin)->post(route('teacher-schedules.store'), [
            'school_id' => $schoolId,
            'teacher_id' => $teacherId,
            'academic_year' => '2026/2027',
            'status' => 'draft',
            'visibility' => 'authenticated',
            'slots' => [
                [
                    'slot_index' => 1,
                    'label' => '08:00-09:00',
                    'starts_at' => '08:00',
                    'ends_at' => '09:00',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('teacher_schedules', [
            'school_id' => $schoolId,
            'teacher_id' => $teacherId,
            'academic_year' => '2026/2027',
        ]);
    }

    public function test_admin_without_default_school_must_select_school_before_creating_teacher_schedule(): void
    {
        $admin = $this->createAdminWithoutSchool();
        [, $teacherId] = $this->bootstrapTeacherForSchool(true);

        $response = $this->actingAs($admin)->from(route('teacher-schedules.create'))->post(route('teacher-schedules.store'), [
            'teacher_id' => $teacherId,
            'academic_year' => '2026/2027',
            'status' => 'draft',
            'visibility' => 'authenticated',
            'slots' => [
                [
                    'slot_index' => 1,
                    'label' => '08:00-09:00',
                    'starts_at' => '08:00',
                    'ends_at' => '09:00',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('school_id');
        $this->assertDatabaseCount('teacher_schedules', 0);
    }

    public function test_admin_without_default_school_create_form_lists_legacy_teacher_in_dropdown(): void
    {
        $admin = $this->createAdminWithoutSchool();
        $schoolId = $this->createSchool();
        [, $teacherId] = $this->bootstrapTeacherForSchool(false, false, $schoolId, 'Legacy Teacher');

        $response = $this->actingAs($admin)->get(route('teacher-schedules.create'));

        $response->assertOk();
        $response->assertSee('Legacy Teacher');
        $this->assertDatabaseHas('teachers', ['id' => $teacherId]);
    }

    private function createAdminWithoutSchool(): User
    {
        $admin = User::factory()->create([
            'must_change_password' => false,
            'school_id' => null,
        ]);

        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        return $admin;
    }

    private function createSchool(): int
    {
        return DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function bootstrapTeacherForSchool(
        bool $setTeacherUserSchool,
        bool $attachSection = true,
        ?int $schoolId = null,
        string $teacherName = 'Teacher One'
    ): array {
        $schoolId ??= $this->createSchool();

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

        $specializationId = DB::table('specializations')->insertGetId([
            'name' => 'Math',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $teacherUser = User::factory()->create([
            'must_change_password' => false,
            'school_id' => $setTeacherUserSchool ? $schoolId : null,
        ]);

        $teacherId = DB::table('teachers')->insertGetId([
            'user_id' => $teacherUser->id,
            'specialization_id' => $specializationId,
            'name' => json_encode([
                'fr' => $teacherName,
                'ar' => $teacherName,
                'en' => $teacherName,
            ]),
            'gender' => 1,
            'joining_date' => now()->toDateString(),
            'address' => 'Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($attachSection) {
            DB::table('teacher_section')->insert([
                'teacher_id' => $teacherId,
                'section_id' => $sectionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$schoolId, $teacherId];
    }
}
