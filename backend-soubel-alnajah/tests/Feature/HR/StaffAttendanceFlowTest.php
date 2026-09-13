<?php

namespace Tests\Feature\HR;

use App\Models\HR\Employee;
use App\Models\HR\StaffAttendance;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class StaffAttendanceFlowTest extends TestCase
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

        foreach (['admin', 'employee'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    public function test_admin_can_create_employee(): void
    {
        [$admin, $schoolId] = $this->bootstrapSchoolAdmin('A');

        $response = $this->actingAs($admin)->post(route('employees.store'), [
            'name' => 'محمد الحارس',
            'job_title' => 'حارس',
            'phone' => '0550001122',
            'is_active' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('employees', [
            'school_id' => $schoolId,
            'name' => 'محمد الحارس',
            'job_title' => 'حارس',
        ]);
    }

    public function test_admin_can_create_employee_with_login_account(): void
    {
        [$admin, $schoolId] = $this->bootstrapSchoolAdmin('A');

        $response = $this->actingAs($admin)->post(route('employees.store'), [
            'name' => 'سعاد الإدارية',
            'job_title' => 'إدارة',
            'create_account' => 1,
            'email' => 'employee-new@example.test',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $user = DB::table('users')->where('email', 'employee-new@example.test')->first();
        $this->assertNotNull($user);
        $this->assertDatabaseHas('employees', [
            'school_id' => $schoolId,
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_can_record_attendance_for_employee_and_teacher(): void
    {
        [$admin, $schoolId, $teacherId] = $this->bootstrapSchoolAdmin('A');
        $employeeId = $this->createEmployee($schoolId, 'موظف أ');

        $emp = $this->actingAs($admin)->postJson(route('staff-attendance.update'), [
            'staff_type' => 'employee',
            'staff_id' => $employeeId,
            'status' => StaffAttendance::LATE,
            'date' => '2026-09-10',
        ]);
        $emp->assertStatus(200);

        $teacher = $this->actingAs($admin)->postJson(route('staff-attendance.update'), [
            'staff_type' => 'teacher',
            'staff_id' => $teacherId,
            'status' => StaffAttendance::ABSENT,
            'date' => '2026-09-10',
        ]);
        $teacher->assertStatus(200);

        $this->assertDatabaseHas('staff_attendances', [
            'school_id' => $schoolId,
            'staffable_type' => 'employee',
            'staffable_id' => $employeeId,
            'date' => '2026-09-10',
            'status' => StaffAttendance::LATE,
        ]);
        $this->assertDatabaseHas('staff_attendances', [
            'school_id' => $schoolId,
            'staffable_type' => 'teacher',
            'staffable_id' => $teacherId,
            'status' => StaffAttendance::ABSENT,
        ]);
    }

    public function test_recording_is_idempotent_per_day(): void
    {
        [$admin, $schoolId] = $this->bootstrapSchoolAdmin('A');
        $employeeId = $this->createEmployee($schoolId, 'موظف ب');

        foreach ([StaffAttendance::ABSENT, StaffAttendance::PRESENT] as $status) {
            $this->actingAs($admin)->postJson(route('staff-attendance.update'), [
                'staff_type' => 'employee',
                'staff_id' => $employeeId,
                'status' => $status,
                'date' => '2026-09-11',
            ])->assertStatus(200);
        }

        // صف واحد فقط لليوم نفسه، بالحالة الأخيرة.
        $this->assertSame(1, DB::table('staff_attendances')
            ->where('staffable_type', 'employee')->where('staffable_id', $employeeId)
            ->where('date', '2026-09-11')->count());
        $this->assertDatabaseHas('staff_attendances', [
            'staffable_id' => $employeeId,
            'date' => '2026-09-11',
            'status' => StaffAttendance::PRESENT,
        ]);
    }

    public function test_admin_cannot_record_attendance_for_employee_of_another_school(): void
    {
        [$admin] = $this->bootstrapSchoolAdmin('A');
        [, $schoolB] = $this->bootstrapSchoolAdmin('B');
        $foreignEmployeeId = $this->createEmployee($schoolB, 'موظف أجنبي');

        $response = $this->actingAs($admin)->postJson(route('staff-attendance.update'), [
            'staff_type' => 'employee',
            'staff_id' => $foreignEmployeeId,
            'status' => StaffAttendance::ABSENT,
            'date' => '2026-09-12',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('staff_attendances', [
            'staffable_type' => 'employee',
            'staffable_id' => $foreignEmployeeId,
        ]);
    }

    public function test_bulk_update_sets_all_staff_for_the_day(): void
    {
        [$admin, $schoolId, $teacherId] = $this->bootstrapSchoolAdmin('A');
        $e1 = $this->createEmployee($schoolId, 'موظف 1');
        $e2 = $this->createEmployee($schoolId, 'موظف 2');

        $response = $this->actingAs($admin)->postJson(route('staff-attendance.bulk'), [
            'status' => StaffAttendance::PRESENT,
            'date' => '2026-09-13',
        ]);

        $response->assertStatus(200);
        // أستاذ + موظفان = 3 سجلات
        $this->assertSame(3, DB::table('staff_attendances')->where('date', '2026-09-13')->count());
        $this->assertDatabaseHas('staff_attendances', [
            'staffable_type' => 'teacher', 'staffable_id' => $teacherId,
            'date' => '2026-09-13', 'status' => StaffAttendance::PRESENT,
        ]);
    }

    public function test_record_data_lists_only_current_school_staff(): void
    {
        [$admin, $schoolId] = $this->bootstrapSchoolAdmin('A');
        $this->createEmployee($schoolId, 'موظف داخلي');
        [, $schoolB] = $this->bootstrapSchoolAdmin('B');
        $this->createEmployee($schoolB, 'موظف خارجي');

        $response = $this->actingAs($admin)->getJson(route('staff-attendance.data', ['date' => '2026-09-10']));
        $response->assertStatus(200);

        $names = collect($response->json('staff'))->pluck('name');
        $this->assertTrue($names->contains('موظف داخلي'));
        $this->assertFalse($names->contains('موظف خارجي'));
    }

    public function test_report_page_renders_for_admin(): void
    {
        [$admin, $schoolId] = $this->bootstrapSchoolAdmin('A');
        $this->createEmployee($schoolId, 'موظف تقرير');

        $response = $this->actingAs($admin)->get(route('staff-attendance.report'));
        $response->assertStatus(200);
        $response->assertViewHas('rows');
    }

    public function test_non_admin_cannot_access_staff_attendance(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($user)->get(route('staff-attendance.record'));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }

    private function bootstrapSchoolAdmin(string $suffix): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        // أستاذ مرتبط بالمدرسة عبر حسابه
        $specId = DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => 'Spec', 'ar' => 'تخصص', 'en' => 'Spec']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $teacherUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'T ' . $suffix, 'ar' => 'أستاذ ' . $suffix, 'en' => 'T ' . $suffix]),
            'email' => 'teacher-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $teacherId = DB::table('teachers')->insertGetId([
            'user_id' => $teacherUserId,
            'specialization_id' => $specId,
            'name' => json_encode(['fr' => 'Teacher ' . $suffix, 'ar' => 'أستاذ ' . $suffix, 'en' => 'Teacher ' . $suffix]),
            'gender' => 1,
            'joining_date' => '2024-09-01',
            'address' => 'Addr',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$admin, $schoolId, $teacherId];
    }

    private function createEmployee(int $schoolId, string $name): int
    {
        return Employee::query()->create([
            'school_id' => $schoolId,
            'name' => $name,
            'job_title' => 'وظيفة',
            'is_active' => true,
        ])->id;
    }
}
