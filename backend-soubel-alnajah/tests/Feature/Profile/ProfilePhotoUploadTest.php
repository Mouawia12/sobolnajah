<?php

namespace Tests\Feature\Profile;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class ProfilePhotoUploadTest extends TestCase
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

    /**
     * @dataProvider roleProvider
     */
    public function test_supported_roles_can_upload_profile_photo(string $role): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole($role);

        $response = $this
            ->from(route('home'))
            ->actingAs($user)
            ->post(route('profile.photo.update'), [
                'profile_photo' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
            ]);

        $response->assertStatus(302);

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    public function test_profile_photo_upload_rejects_invalid_file(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('student');

        $response = $this
            ->from(route('home'))
            ->actingAs($user)
            ->post(route('profile.photo.update'), [
                'profile_photo' => UploadedFile::fake()->create('bad.pdf', 150, 'application/pdf'),
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('profile_photo');
    }

    public function test_uploading_new_profile_photo_deletes_old_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['must_change_password' => false]);
        $user->attachRole('teacher');

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'profile_photo' => UploadedFile::fake()->image('first.jpg', 320, 320),
        ])->assertStatus(302);

        $user->refresh();
        $firstPath = $user->profile_photo_path;
        Storage::disk('public')->assertExists($firstPath);

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'profile_photo' => UploadedFile::fake()->image('second.jpg', 320, 320),
        ])->assertStatus(302);

        $user->refresh();
        $this->assertNotSame($firstPath, $user->profile_photo_path);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    public static function roleProvider(): array
    {
        return [
            ['student'],
            ['guardian'],
            ['teacher'],
            ['accountant'],
            ['admin'],
        ];
    }
}
