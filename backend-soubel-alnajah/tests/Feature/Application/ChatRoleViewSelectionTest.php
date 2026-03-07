<?php

namespace Tests\Feature\Application;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class ChatRoleViewSelectionTest extends TestCase
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

        foreach (['admin', 'teacher', 'accountant', 'student', 'guardian'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    public function test_student_opens_student_chat_view_not_admin_layout(): void
    {
        $student = User::factory()->create(['must_change_password' => false]);
        $student->attachRole('student');

        $response = $this->actingAs($student)->get(route('Chats.index'));

        $response->assertStatus(200);
        $response->assertViewIs('chat.student');
    }

    public function test_guardian_opens_guardian_chat_view_not_admin_layout(): void
    {
        $guardian = User::factory()->create(['must_change_password' => false]);
        $guardian->attachRole('guardian');

        $response = $this->actingAs($guardian)->get(route('Chats.index'));

        $response->assertStatus(200);
        $response->assertViewIs('chat.guardian');
    }

    public function test_admin_keeps_admin_chat_view(): void
    {
        $admin = User::factory()->create(['must_change_password' => false]);
        $admin->attachRole('admin');

        $response = $this->actingAs($admin)->get(route('Chats.index'));

        $response->assertStatus(200);
        $response->assertViewIs('chat.admin');
    }
}
