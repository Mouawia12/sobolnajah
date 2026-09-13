<?php

namespace Tests\Feature\Print;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class StructurePrintTest extends TestCase
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

    public function test_school_structure_print_pages_render(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'مدرسة أ', 'en' => 'S']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $gradeId = DB::table('schoolgrades')->insertGetId([
            'school_id' => $schoolId, 'name_grade' => json_encode(['fr' => 'G', 'ar' => 'مستوى أول', 'en' => 'G']),
            'notes' => json_encode(['fr' => 'N', 'ar' => 'ن', 'en' => 'N']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $classroomId = DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId, 'grade_id' => $gradeId,
            'name_class' => json_encode(['fr' => 'C', 'ar' => 'قسم أول', 'en' => 'C']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('sections')->insert([
            'school_id' => $schoolId, 'grade_id' => $gradeId, 'classroom_id' => $classroomId,
            'name_section' => json_encode(['fr' => 'S', 'ar' => 'فوج أول', 'en' => 'S']), 'Status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('Schools.print'))->assertStatus(200)->assertSee('مدرسة أ');
        $this->actingAs($admin)->get(route('Schoolgrades.print'))->assertStatus(200)->assertSee('مستوى أول');
        $this->actingAs($admin)->get(route('Classes.print'))->assertStatus(200)->assertSee('قسم أول');
        $this->actingAs($admin)->get(route('Sections.print'))->assertStatus(200)->assertSee('فوج أول');
    }

    public function test_structure_print_pdf_downloads(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'S', 'ar' => 'مدرسة', 'en' => 'S']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolId]);

        $response = $this->actingAs($admin)->get(route('Schools.print', ['format' => 'pdf']));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', strtolower((string) $response->headers->get('content-type')));
    }
}
