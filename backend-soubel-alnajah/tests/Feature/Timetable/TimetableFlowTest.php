<?php

namespace Tests\Feature\Timetable;

use App\Models\Role;
use App\Models\Timetable\Timetable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class TimetableFlowTest extends TestCase
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

    public function test_admin_can_create_timetable_with_entries_for_own_school(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrapAdminWithSection();

        $response = $this->actingAs($admin)->post(route('timetables.store'), [
            'section_id' => $sectionId,
            'academic_year' => '2026-2027',
            'title' => 'جدول 1 متوسط',
            'is_published' => 1,
            'entries' => [
                [
                    'day_of_week' => 1,
                    'period_index' => 1,
                    'starts_at' => '08:00',
                    'ends_at' => '08:45',
                    'subject_name' => 'Math',
                    'room_name' => 'A1',
                ],
                [
                    'day_of_week' => 1,
                    'period_index' => 2,
                    'starts_at' => '09:00',
                    'ends_at' => '09:45',
                    'subject_name' => 'Arabic',
                    'room_name' => 'A1',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('timetables', [
            'school_id' => $schoolId,
            'section_id' => $sectionId,
            'academic_year' => '2026-2027',
            'is_published' => 1,
        ]);
        $this->assertDatabaseCount('timetable_entries', 2);
    }

    public function test_admin_cannot_create_timetable_for_section_of_another_school(): void
    {
        [$admin] = $this->bootstrapAdminWithSection();

        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $gradeB = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolB,
            'name_grade' => json_encode(['fr' => 'G2', 'ar' => 'م2', 'en' => 'G2']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $classB = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeB,
            'name_class' => json_encode(['fr' => 'C2', 'ar' => 'ق2', 'en' => 'C2']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $sectionB = DB::table('sections')->insertGetId([
            'school_id' => $schoolB,
            'grade_id' => $gradeB,
            'classroom_id' => $classB,
            'name_section' => json_encode(['fr' => 'S2', 'ar' => 'ف2', 'en' => 'S2']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('timetables.store'), [
            'section_id' => $sectionB,
            'academic_year' => '2026-2027',
            'entries' => [[
                'day_of_week' => 1,
                'period_index' => 1,
                'subject_name' => 'Math',
            ]],
        ]);

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseCount('timetables', 0);
    }

    public function test_admin_can_open_timetable_print_page_for_own_school(): void
    {
        [$admin, $schoolId, $sectionId] = $this->bootstrapAdminWithSection();

        $timetable = Timetable::query()->create([
            'school_id' => $schoolId,
            'section_id' => $sectionId,
            'academic_year' => '2026-2027',
            'title' => 'Public Timetable',
            'is_published' => true,
            'published_at' => now(),
        ]);
        DB::table('timetable_entries')->insert([
            'timetable_id' => $timetable->id,
            'day_of_week' => 1,
            'period_index' => 1,
            'subject_name' => 'Physics',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('timetables.print', $timetable));
        $this->assertTrue(in_array($response->status(), [200, 302], true));
    }

    public function test_public_timetables_index_lists_only_sections_with_published_timetables(): void
    {
        [, $schoolId, $sectionPublished] = $this->bootstrapAdminWithSection();

        $gradeId = (int) DB::table('sections')->where('id', $sectionPublished)->value('grade_id');
        $classroomId = (int) DB::table('sections')->where('id', $sectionPublished)->value('classroom_id');

        $sectionUnpublished = DB::table('sections')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S2', 'ar' => 'ف2', 'en' => 'S2']),
            'Status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Timetable::query()->create([
            'school_id' => $schoolId,
            'section_id' => $sectionPublished,
            'academic_year' => '2026-2027',
            'title' => 'Published',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Timetable::query()->create([
            'school_id' => $schoolId,
            'section_id' => $sectionUnpublished,
            'academic_year' => '2026-2027',
            'title' => 'Hidden',
            'is_published' => false,
        ]);

        $response = $this->get(route('public.timetables.index'));
        $response->assertStatus(200);
        $response->assertViewIs('front-end.timetables.index');
        $response->assertViewHas('sections', function ($sections) use ($sectionPublished, $sectionUnpublished) {
            $ids = $sections->pluck('id')->all();
            return in_array($sectionPublished, $ids, true)
                && !in_array($sectionUnpublished, $ids, true);
        });
    }

    public function test_public_sections_cache_is_invalidated_after_timetable_create(): void
    {
        [$admin, , $sectionId] = $this->bootstrapAdminWithSection();
        Cache::put('public:timetables:sections', ['stale'], now()->addMinutes(10));
        $this->assertTrue(Cache::has('public:timetables:sections'));

        $response = $this->actingAs($admin)->post(route('timetables.store'), [
            'section_id' => $sectionId,
            'academic_year' => '2026-2027',
            'title' => 'Cache reset',
            'is_published' => 1,
            'entries' => [
                [
                    'day_of_week' => 1,
                    'period_index' => 1,
                    'subject_name' => 'Math',
                ],
            ],
        ]);

        $response->assertStatus(302);
        $this->assertFalse(Cache::has('public:timetables:sections'));
    }

    public function test_public_timetables_index_does_not_eager_load_entries_relation(): void
    {
        [, $schoolId, $sectionId] = $this->bootstrapAdminWithSection();

        $timetable = Timetable::query()->create([
            'school_id' => $schoolId,
            'section_id' => $sectionId,
            'academic_year' => '2026-2027',
            'title' => 'Index Perf',
            'is_published' => true,
            'published_at' => now(),
        ]);
        DB::table('timetable_entries')->insert([
            'timetable_id' => $timetable->id,
            'day_of_week' => 1,
            'period_index' => 1,
            'subject_name' => 'Math',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get(route('public.timetables.index'));
        $response->assertStatus(200);
        $response->assertViewHas('timetables', function ($paginator) use ($timetable) {
            $item = $paginator->firstWhere('id', $timetable->id);
            if (!$item) {
                return false;
            }

            return !$item->relationLoaded('entries');
        });
    }

    private function bootstrapAdminWithSection(): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
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
}
