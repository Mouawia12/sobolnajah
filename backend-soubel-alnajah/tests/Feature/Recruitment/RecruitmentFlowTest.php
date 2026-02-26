<?php

namespace Tests\Feature\Recruitment;

use App\Models\Recruitment\JobPost;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecruitmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_apply_to_published_job_with_secure_cv_upload(): void
    {
        Storage::fake('local');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $post = JobPost::query()->create([
            'school_id' => $schoolId,
            'slug' => 'math-teacher-' . Str::random(4),
            'title' => 'Math Teacher',
            'description' => 'We are hiring.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->post(route('public.jobs.apply', $post), [
            'full_name' => 'Candidate Name',
            'phone' => '0550000001',
            'email' => 'candidate@example.test',
            'website' => '',
            'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ]);

        $response->assertStatus(302);

        $application = DB::table('job_applications')->where('job_post_id', $post->id)->first();
        $this->assertNotNull($application);
        $this->assertSame('new', $application->status);
        $this->assertStringContainsString('private/recruitment/' . $schoolId . '/' . $post->id, $application->cv_path);
        Storage::disk('local')->assertExists($application->cv_path);
    }

    public function test_honeypot_blocks_spam_submission(): void
    {
        Storage::fake('local');

        $schoolId = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'School A', 'ar' => 'مدرسة أ', 'en' => 'School A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $post = JobPost::query()->create([
            'school_id' => $schoolId,
            'slug' => 'science-teacher-' . Str::random(4),
            'title' => 'Science Teacher',
            'description' => 'We are hiring.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->post(route('public.jobs.apply', $post), [
            'full_name' => 'Spam Bot',
            'phone' => '0550000002',
            'email' => 'spam@example.test',
            'website' => 'https://spam.test',
            'cv' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['website']);
        $this->assertSame(0, DB::table('job_applications')->count());
    }

    public function test_admin_cannot_access_cv_download_for_another_school(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'admin']);
        $admin->attachRole('admin');

        $schoolA = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'A', 'ar' => 'أ', 'en' => 'A']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $schoolB = DB::table('schools')->insertGetId([
            'name_school' => json_encode(['fr' => 'B', 'ar' => 'ب', 'en' => 'B']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin->update(['school_id' => $schoolA]);

        $post = JobPost::query()->create([
            'school_id' => $schoolB,
            'slug' => 'arabic-teacher-' . Str::random(4),
            'title' => 'Arabic Teacher',
            'description' => 'Hiring',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $path = 'private/recruitment/' . $schoolB . '/' . $post->id . '/cv.pdf';
        Storage::disk('local')->put($path, 'pdf-content');

        $applicationId = DB::table('job_applications')->insertGetId([
            'school_id' => $schoolB,
            'job_post_id' => $post->id,
            'full_name' => 'Candidate B',
            'phone' => '0551234567',
            'email' => 'candidateb@example.test',
            'status' => 'new',
            'cv_path' => $path,
            'cv_original_name' => 'cv.pdf',
            'cv_mime' => 'application/pdf',
            'cv_size' => 11,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('recruitment.applications.cv', ['jobApplication' => $applicationId]));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }

    public function test_non_admin_cannot_access_job_posts_admin_page(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($user)->get(route('JobPosts.index'));
        $this->assertTrue(in_array($response->status(), [302, 403, 404], true));
    }
}
