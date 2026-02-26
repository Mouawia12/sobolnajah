<?php

namespace Tests\Feature\School;

use App\Http\Controllers\School\SchoolgradeController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LookupCacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_schoolgrade_lookup_cache_is_invalidated_after_grade_creation(): void
    {
        [$admin, $schoolId] = $this->bootstrapAdminWithSchool();
        $cacheKey = sprintf('lookup:school:%d:grades', $schoolId);
        $controller = app(SchoolgradeController::class);
        $this->be($admin);

        $initialLookup = $controller->listBySchool($schoolId);
        $this->assertCount(0, $initialLookup);
        $this->assertTrue(Cache::has($cacheKey));

        $storeResponse = $this->actingAs($admin)->post(route('Schoolgrades.store'), [
            'school_id' => $schoolId,
            'name_gradefr' => 'Grade 1',
            'name_gradear' => 'المستوى 1',
            'notesfr' => 'Primary level',
            'notesar' => 'الطور الابتدائي',
        ]);
        $storeResponse->assertStatus(302);
        $this->assertFalse(Cache::has($cacheKey));

        $afterLookup = $controller->listBySchool($schoolId);
        $this->assertCount(1, $afterLookup);
    }

    public function test_exam_admin_lookup_caches_are_invalidated_after_grade_and_classroom_creation(): void
    {
        [$admin, $schoolId] = $this->bootstrapAdminWithSchool();
        $this->actingAs($admin);

        $examGradesKey = sprintf('exam:school:%d:grades', $schoolId);
        $examClassroomsKey = sprintf('exam:school:%d:classrooms', $schoolId);

        Cache::put($examGradesKey, ['cached'], now()->addMinutes(15));
        Cache::put($examClassroomsKey, ['cached'], now()->addMinutes(15));

        $gradeResponse = $this->post(route('Schoolgrades.store'), [
            'school_id' => $schoolId,
            'name_gradefr' => 'Grade X',
            'name_gradear' => 'المستوى X',
            'notesfr' => 'Notes',
            'notesar' => 'ملاحظات',
        ]);
        $gradeResponse->assertStatus(302);
        $this->assertFalse(Cache::has($examGradesKey));

        $gradeId = (int) DB::table('schoolgrades')->where('school_id', $schoolId)->value('id');
        Cache::put($examClassroomsKey, ['cached-again'], now()->addMinutes(15));

        $classroomResponse = $this->post(route('Classes.store'), [
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_classfr' => 'Class X',
            'name_classar' => 'قسم X',
        ]);
        $classroomResponse->assertStatus(302);
        $this->assertFalse(Cache::has($examClassroomsKey));
    }

    public function test_exam_admin_grade_cache_is_invalidated_after_grade_creation(): void
    {
        [$admin, $schoolId] = $this->bootstrapAdminWithSchool();
        $examCacheKey = sprintf('exam:school:%d:grades', $schoolId);

        Cache::put($examCacheKey, collect([['id' => 999]]), now()->addMinutes(15));
        $this->assertTrue(Cache::has($examCacheKey));

        $response = $this->actingAs($admin)->post(route('Schoolgrades.store'), [
            'school_id' => $schoolId,
            'name_gradefr' => 'Grade 2',
            'name_gradear' => 'المستوى 2',
            'notesfr' => 'Middle level',
            'notesar' => 'الطور المتوسط',
        ]);

        $response->assertStatus(302);
        $this->assertFalse(Cache::has($examCacheKey));
    }

    private function bootstrapAdminWithSchool(): array
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin->update(['school_id' => $schoolId]);

        return [$admin, $schoolId];
    }
}
