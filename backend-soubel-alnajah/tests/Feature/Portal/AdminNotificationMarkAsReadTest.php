<?php

namespace Tests\Feature\Portal;

use App\Models\Role;
use App\Models\User;
use App\Notifications\StudentSchoolCertificateNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class AdminNotificationMarkAsReadTest extends TestCase
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

    public function test_admin_can_open_certificate_notification_details_without_server_error(): void
    {
        $admin = User::factory()->create(['must_change_password' => false, 'school_id' => 1]);
        $admin->attachRole('admin');

        $student = User::factory()->create(['must_change_password' => false, 'school_id' => 1]);

        $admin->notify(new StudentSchoolCertificateNotification(
            $student,
            [
                'year' => '2025/2026',
                'purpose' => 'enrollment',
                'copies' => 1,
                'preferred_language' => 'ar',
                'delivery_method' => 'printed',
                'notes' => '',
                'requested_at' => now()->toDateTimeString(),
            ],
            'Nom Test',
            'اسم تجريبي'
        ));

        $notificationId = $admin->notifications()->latest()->value('id');
        $this->assertNotNull($notificationId);

        $response = $this
            ->actingAs($admin)
            ->post(route('markAsRead', ['id' => $notificationId]));

        $response->assertStatus(200);
        $response->assertSee('2025/2026');
    }
}
