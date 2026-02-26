<?php

namespace Tests\Feature\Application;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class ChatIndexPerformanceTest extends TestCase
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

    public function test_chat_index_limits_available_users_and_scopes_to_school(): void
    {
        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School B', 'ar' => 'مدرسة ب', 'en' => 'School B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $currentUser = User::factory()->create([
            'must_change_password' => false,
            'school_id' => $schoolA,
        ]);

        for ($i = 1; $i <= 85; $i++) {
            User::factory()->create([
                'must_change_password' => false,
                'school_id' => $schoolA,
                'email' => 'same-school-' . $i . '@example.test',
            ]);
        }

        $outsideUser = User::factory()->create([
            'must_change_password' => false,
            'school_id' => $schoolB,
            'email' => 'outside-school@example.test',
        ]);

        $response = $this->actingAs($currentUser)->get(route('Chats.index'));

        $response->assertStatus(200);
        $response->assertViewHas('availableUsers', function ($availableUsers) use ($outsideUser) {
            return $availableUsers->count() === 60
                && !$availableUsers->pluck('id')->contains($outsideUser->id);
        });
    }
}
