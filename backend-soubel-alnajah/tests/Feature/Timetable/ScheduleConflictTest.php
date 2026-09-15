<?php

namespace Tests\Feature\Timetable;

use App\Models\Role;
use App\Models\User;
use App\Services\ScheduleConflictService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class ScheduleConflictTest extends TestCase
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

    public function test_detects_teacher_double_booking_across_sections(): void
    {
        [$admin, $schoolId, $sectionA, $sectionB, $teacherId] = $this->bootstrap();

        // نفس الأستاذ في القسمين، اليوم 1 الحصة 1 => تعارض
        $ttA = $this->timetable($schoolId, $sectionA);
        $ttB = $this->timetable($schoolId, $sectionB);
        $this->entry($ttA, 1, 1, $teacherId, 'رياضيات');
        $this->entry($ttB, 1, 1, $teacherId, 'رياضيات');

        $conflicts = app(ScheduleConflictService::class)->forSchool($schoolId);
        $this->assertCount(1, $conflicts);
        $this->assertSame('teacher', $conflicts->first()['type']);

        $response = $this->actingAs($admin)->get(route('timetables.conflicts'));
        $response->assertStatus(200);
        $response->assertSee(trans('timetable.conflicts.teacher'));
    }

    public function test_no_conflict_when_teacher_in_different_periods(): void
    {
        [$admin, $schoolId, $sectionA, $sectionB, $teacherId] = $this->bootstrap();

        $ttA = $this->timetable($schoolId, $sectionA);
        $ttB = $this->timetable($schoolId, $sectionB);
        $this->entry($ttA, 1, 1, $teacherId, 'رياضيات');
        $this->entry($ttB, 1, 2, $teacherId, 'رياضيات'); // حصة مختلفة

        $this->assertCount(0, app(ScheduleConflictService::class)->forSchool($schoolId));
    }

    public function test_detects_room_double_booking(): void
    {
        [$admin, $schoolId, $sectionA, $sectionB, $teacherId] = $this->bootstrap();
        $t2 = DB::table('teachers')->insertGetId([
            'user_id' => $this->teacherUser($schoolId), 'specialization_id' => DB::table('specializations')->value('id'),
            'name' => json_encode(['fr' => 'T2', 'ar' => 'أستاذ2', 'en' => 'T2']), 'gender' => 1,
            'joining_date' => '2024-09-01', 'address' => 'A', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $ttA = $this->timetable($schoolId, $sectionA);
        $ttB = $this->timetable($schoolId, $sectionB);
        // أستاذان مختلفان لكن نفس القاعة، نفس اليوم والحصة => تعارض قاعة
        $this->entry($ttA, 2, 1, $teacherId, 'رياضيات', 'قاعة 5');
        $this->entry($ttB, 2, 1, $t2, 'علوم', 'قاعة 5');

        $conflicts = app(ScheduleConflictService::class)->forSchool($schoolId);
        $this->assertSame('room', $conflicts->firstWhere('type', 'room')['type'] ?? null);
    }

    private function bootstrap(): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'مدرسة', 'en' => 'S']), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId, 'name_grade' => json_encode(['fr' => 'G', 'ar' => 'م', 'en' => 'G']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'name_class' => json_encode(['fr' => 'C', 'ar' => 'ق', 'en' => 'C']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $mk = fn ($tag) => DB::table('sections')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => $tag, 'ar' => $tag, 'en' => $tag]), 'Status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $sectionA = $mk('A');
        $sectionB = $mk('B');
        $specId = DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => 'Math', 'ar' => 'رياضيات', 'en' => 'Math']), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $teacherId = DB::table('teachers')->insertGetId([
            'user_id' => $this->teacherUser($schoolId), 'specialization_id' => $specId,
            'name' => json_encode(['fr' => 'Prof', 'ar' => 'الأستاذ كريم', 'en' => 'Prof']), 'gender' => 1,
            'joining_date' => '2024-09-01', 'address' => 'A', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$admin, $schoolId, $sectionA, $sectionB, $teacherId];
    }

    private function teacherUser(int $schoolId): int
    {
        return DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'T', 'ar' => 'أستاذ', 'en' => 'T']),
            'email' => 'teach-' . uniqid() . '@example.test', 'password' => bcrypt('x'),
            'school_id' => $schoolId, 'must_change_password' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function timetable(int $schoolId, int $sectionId): int
    {
        return DB::table('timetables')->insertGetId([
            'school_id' => $schoolId, 'section_id' => $sectionId, 'academic_year' => '2026-2027',
            'is_published' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function entry(int $timetableId, int $day, int $period, int $teacherId, string $subject, ?string $room = null): void
    {
        DB::table('timetable_entries')->insert([
            'timetable_id' => $timetableId, 'day_of_week' => $day, 'period_index' => $period,
            'subject_name' => $subject, 'teacher_id' => $teacherId, 'room_name' => $room,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
