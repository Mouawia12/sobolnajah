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

class InscriptionIndexFiltersTest extends TestCase
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

    public function test_inscriptions_index_is_school_scoped_and_paginated(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolA, $gradeA, $classA] = $this->createSchoolHierarchy('A');
        [$schoolB, $gradeB, $classB] = $this->createSchoolHierarchy('B');
        $admin->update(['school_id' => $schoolA]);

        for ($i = 1; $i <= 25; $i++) {
            $this->createInscription($schoolA, $gradeA, $classA, 'A' . $i, 'procec');
        }
        $this->createInscription($schoolB, $gradeB, $classB, 'B1', 'accept');

        $response = $this->actingAs($admin)->get(route('Inscriptions.index'));
        $response->assertStatus(200);
        $response->assertViewHas('Inscription', function ($paginator) {
            return $paginator->count() === 20 && $paginator->total() === 25;
        });
    }

    public function test_inscriptions_index_can_filter_by_status_and_classroom(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolA, $gradeA, $classA1] = $this->createSchoolHierarchy('A');
        [, , $classA2] = $this->createSchoolHierarchy('A2', $schoolA, $gradeA);
        $admin->update(['school_id' => $schoolA]);

        $this->createInscription($schoolA, $gradeA, $classA1, 'X1', 'accept');
        $this->createInscription($schoolA, $gradeA, $classA1, 'X2', 'procec');
        $this->createInscription($schoolA, $gradeA, $classA2, 'Y1', 'accept');

        $response = $this->actingAs($admin)->get(route('Inscriptions.index', [
            'status' => 'accept',
            'classroom_id' => $classA1,
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('Inscription', function ($paginator) {
            return $paginator->total() === 1;
        });
    }

    public function test_inscriptions_index_renders_empty_state_when_filter_returns_no_results(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        [$schoolA, $gradeA, $classA] = $this->createSchoolHierarchy('A');
        $admin->update(['school_id' => $schoolA]);

        $this->createInscription($schoolA, $gradeA, $classA, 'X1', 'accept');

        $response = $this->actingAs($admin)->get(route('Inscriptions.index', [
            'q' => 'no-match-keyword',
        ]));

        $response->assertStatus(200);
        $response->assertSee('لا توجد تسجيلات مطابقة للفلترة الحالية.');
        $response->assertViewHas('Inscription', function ($paginator) {
            return $paginator->total() === 0;
        });
    }

    public function test_public_inscription_page_does_not_load_admin_inscriptions_dataset(): void
    {
        [$schoolA, $gradeA, $classA] = $this->createSchoolHierarchy('A');

        for ($i = 1; $i <= 10; $i++) {
            $this->createInscription($schoolA, $gradeA, $classA, 'G' . $i, 'procec');
        }

        $response = $this->get(route('Inscriptions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('front-end.inscription');
        $response->assertViewMissing('Inscription');
        $response->assertViewMissing('Classrooms');
        $response->assertViewHas('School');
    }

    private function createSchoolHierarchy(string $suffix, ?int $schoolId = null, ?int $gradeId = null): array
    {
        if (!$schoolId) {
            $schoolId = DB::table('schools')->insertGetId([
                'name_school' => json_encode(['fr' => 'School ' . $suffix, 'ar' => 'مدرسة ' . $suffix, 'en' => 'School ' . $suffix]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$gradeId) {
            $gradeId = DB::table('schoolgrades')->insertGetId([
                'school_id' => $schoolId,
                'name_grade' => json_encode(['fr' => 'G' . $suffix, 'ar' => 'م' . $suffix, 'en' => 'G' . $suffix]),
                'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C' . $suffix, 'ar' => 'ق' . $suffix, 'en' => 'C' . $suffix]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$schoolId, $gradeId, $classroomId];
    }

    private function createInscription(int $schoolId, int $gradeId, int $classroomId, string $suffix, string $status): void
    {
        DB::table('inscriptions')->insert([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'classroom_id' => $classroomId,
            'inscriptionetat' => 'nouvelleinscription',
            'nomecoleprecedente' => 'old school',
            'dernieresection' => 'section old',
            'moyensannuels' => 12.5,
            'numeronationaletudiant' => 100000000 + random_int(1, 99999),
            'prenom' => json_encode(['fr' => 'Prenom ' . $suffix, 'ar' => 'اسم ' . $suffix, 'en' => 'Name ' . $suffix]),
            'nom' => json_encode(['fr' => 'Nom ' . $suffix, 'ar' => 'لقب ' . $suffix, 'en' => 'Last ' . $suffix]),
            'email' => 'inscription-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'gender' => 1,
            'numtelephone' => 550000000 + random_int(1, 999),
            'datenaissance' => '2012-01-01',
            'lieunaissance' => 'City',
            'wilaya' => 'Wilaya',
            'dayra' => 'Dayra',
            'baladia' => 'Baladia',
            'adresseactuelle' => 'Address',
            'codepostal' => 16000,
            'residenceactuelle' => 'Residence',
            'etatsante' => 'good',
            'identificationmaladie' => 'none',
            'alfdlprsaldr' => 'notes',
            'autresnotes' => null,
            'prenomwali' => 'Parent',
            'nomwali' => 'Name',
            'relationetudiant' => 'father',
            'adressewali' => 'Address',
            'numtelephonewali' => 551000000 + random_int(1, 999),
            'emailwali' => 'parent-' . strtolower($suffix) . '-' . uniqid() . '@example.test',
            'wilayawali' => 'Wilaya',
            'dayrawali' => 'Dayra',
            'baladiawali' => 'Baladia',
            'statu' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
