<?php

namespace Tests\Feature\AgendaScolaire;

use App\Models\Role;
use App\Models\User;
use App\Services\OpenAiTranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class PublicPublicationPerformanceTest extends TestCase
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

    public function test_publication_show_eager_loads_galleries_and_warms_reference_cache(): void
    {
        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradeId = DB::table('grades')->insertGetId([
            'name_grades' => json_encode(['fr' => 'Grade A', 'ar' => 'طور أ', 'en' => 'Grade A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agendaId = DB::table('agenda')->insertGetId([
            'name_agenda' => json_encode(['fr' => 'Agenda A', 'ar' => 'أجندة أ', 'en' => 'Agenda A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publicationId = DB::table('publications')->insertGetId([
            'school_id' => $schoolId,
            'grade_id' => $gradeId,
            'agenda_id' => $agendaId,
            'title' => json_encode(['fr' => 'Title', 'ar' => 'عنوان', 'en' => 'Title']),
            'body' => json_encode(['fr' => 'Body', 'ar' => 'محتوى', 'en' => 'Body']),
            'like' => 99,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('galleries')->insert([
            'publication_id' => $publicationId,
            'agenda_id' => $agendaId,
            'grade_id' => $gradeId,
            'img_url' => json_encode(['a.jpg', 'b.jpg']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::forget('public:publication:grades');
        Cache::forget('public:publication:agendas');

        $response = $this->get(route('Publications.show', $publicationId));
        $response->assertStatus(200);
        $response->assertViewIs('front-end.singlepublication');
        $response->assertViewHas('Publications', function ($publications) {
            $first = $publications->first();
            return $publications->count() === 1
                && $first !== null
                && $first->relationLoaded('galleries');
        });
        $this->assertTrue(Cache::has('public:publication:grades'));
        $this->assertTrue(Cache::has('public:publication:agendas'));
    }

    public function test_publication_reference_cache_is_invalidated_after_grade_and_agenda_updates(): void
    {
        $this->mock(OpenAiTranslationService::class, function ($mock) {
            $mock->shouldReceive('translateToFrenchAndEnglish')
                ->andReturn(['fr' => 'Translated', 'en' => 'Translated']);
        });

        $admin = User::factory()->create([
            'must_change_password' => false,
        ]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        Cache::put('public:publication:grades', ['cached'], now()->addMinutes(15));
        Cache::put('public:publication:agendas', ['cached'], now()->addMinutes(15));

        $gradeResponse = $this->actingAs($admin)->post(route('Grades.store'), [
            'name_gradesar' => 'طور جديد',
        ]);
        $gradeResponse->assertStatus(302);
        $this->assertFalse(Cache::has('public:publication:grades'));
        $this->assertFalse(Cache::has('public:publication:agendas'));

        Cache::put('public:publication:grades', ['cached-again'], now()->addMinutes(15));
        Cache::put('public:publication:agendas', ['cached-again'], now()->addMinutes(15));

        $agendaResponse = $this->actingAs($admin)->post(route('Agendas.store'), [
            'name_agendaar' => 'أجندة جديدة',
        ]);
        $agendaResponse->assertStatus(302);
        $this->assertFalse(Cache::has('public:publication:grades'));
        $this->assertFalse(Cache::has('public:publication:agendas'));
    }
}
