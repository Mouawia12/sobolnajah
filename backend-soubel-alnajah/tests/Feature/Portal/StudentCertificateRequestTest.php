<?php

namespace Tests\Feature\Portal;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class StudentCertificateRequestTest extends TestCase
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

    public function test_student_can_submit_professional_certificate_request_payload(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this
            ->actingAs($user)
            ->from('/home')
            ->post(route('notify', ['id' => $user->id]), [
                'year' => '2025/2026',
                'purpose' => 'administrative',
                'copies' => 2,
                'preferred_language' => 'ar',
                'delivery_method' => 'printed',
                'notes' => 'أحتاج الشهادة لملف إداري.',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $notificationRow = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->first();

        $this->assertNotNull($notificationRow);

        $payload = json_decode($notificationRow->data, true);
        $this->assertSame('2025/2026', $payload['year'] ?? null);
        $this->assertSame('administrative', $payload['purpose'] ?? null);
        $this->assertSame(2, $payload['copies'] ?? null);
        $this->assertSame('ar', $payload['preferred_language'] ?? null);
        $this->assertSame('printed', $payload['delivery_method'] ?? null);
        $this->assertSame('أحتاج الشهادة لملف إداري.', $payload['notes'] ?? null);
    }

    public function test_certificate_request_requires_new_structured_fields(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this
            ->actingAs($user)
            ->from('/home')
            ->post(route('notify', ['id' => $user->id]), [
                'year' => '2025/2026',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'purpose',
            'copies',
            'preferred_language',
            'delivery_method',
        ]);
    }
}
