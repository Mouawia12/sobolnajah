<?php

namespace Tests\Feature\Inscription;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class TeacherIndexFiltersTest extends TestCase
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

    public function test_teachers_index_is_school_scoped_and_paginated(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = $this->createSchool('A');
        $schoolB = $this->createSchool('B');
        $admin->update(['school_id' => $schoolA]);

        $specMath = $this->createSpecialization('Math');

        for ($i = 1; $i <= 25; $i++) {
            $this->createTeacher($schoolA, $specMath, 'A' . $i, $i % 2);
        }
        $this->createTeacher($schoolB, $specMath, 'B1', 1);

        $response = $this->actingAs($admin)->get(route('Teachers.index'));
        $response->assertStatus(200);
        $response->assertViewHas('Teacher', function ($paginator) {
            return $paginator->count() === 20 && $paginator->total() === 25;
        });
    }

    public function test_teachers_index_can_filter_by_specialization_and_gender(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = $this->createSchool('A');
        $admin->update(['school_id' => $schoolA]);

        $specMath = $this->createSpecialization('Math');
        $specScience = $this->createSpecialization('Science');

        $this->createTeacher($schoolA, $specMath, 'M1', 1);
        $this->createTeacher($schoolA, $specMath, 'M2', 0);
        $this->createTeacher($schoolA, $specScience, 'S1', 1);

        $response = $this->actingAs($admin)->get(route('Teachers.index', [
            'specialization_id' => $specMath,
            'gender' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('Teacher', function ($paginator) {
            return $paginator->total() === 1;
        });
    }

    private function createSchool(string $suffix): int
    {
        return DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'School ' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createSpecialization(string $name): int
    {
        return DB::table('specializations')->insertGetId([
            'name' => json_encode(['fr' => $name, 'ar' => $name, 'en' => $name]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTeacher(int $schoolId, int $specializationId, string $suffix, int $gender): void
    {
        $teacherUserId = DB::table('users')->insertGetId([
            'name' => json_encode(['fr' => 'Teacher ' . $suffix, 'ar' => 'أستاذ ' . $suffix, 'en' => 'Teacher ' . $suffix]),
            'email' => 'teacher-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'password' => bcrypt('Secret123!'),
            'school_id' => $schoolId,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('teachers')->insert([
            'user_id' => $teacherUserId,
            'specialization_id' => $specializationId,
            'name' => json_encode(['fr' => 'Prof ' . $suffix, 'ar' => 'أستاذ ' . $suffix, 'en' => 'Teacher ' . $suffix]),
            'gender' => $gender,
            'joining_date' => '2020-01-01',
            'address' => json_encode(['fr' => 'Address', 'ar' => 'عنوان', 'en' => 'Address']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
