<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyJobPostRequest;
use App\Http\Requests\StoreJobPostRequest;
use App\Http\Requests\UpdateJobPostRequest;
use App\Models\Recruitment\JobPost;
use App\Models\School\School;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class JobPostController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index()
    {
        $this->authorize('viewAny', JobPost::class);

        $schoolId = $this->currentSchoolId();
        $search = request('q');
        $status = request('status');

        $jobPosts = JobPost::query()
            ->forSchool($schoolId)
            ->select(['id', 'school_id', 'slug', 'title', 'status', 'published_at', 'created_at'])
            ->when($search, fn ($query) => $query->where('title', 'like', '%' . $search . '%'))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->withCount('applications')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.recruitment.job_posts.index', [
            'jobPosts' => $jobPosts,
            'notify' => $this->notifications(),
            'canPickSchool' => !$schoolId,
            'schools' => !$schoolId
                ? School::query()->select(['id', 'name_school'])->orderBy('name_school')->get()
                : collect(),
            'breadcrumbs' => [
                ['label' => trans('recruitment.breadcrumbs.dashboard'), 'url' => url('/admin')],
                ['label' => trans('recruitment.breadcrumbs.job_posts')],
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', JobPost::class);

        $schoolId = $this->currentSchoolId();

        return view('admin.recruitment.job_posts.create', [
            'notify' => $this->notifications(),
            'canPickSchool' => !$schoolId,
            'schools' => !$schoolId
                ? School::query()->select(['id', 'name_school'])->orderBy('name_school')->get()
                : collect(),
            'breadcrumbs' => [
                ['label' => trans('recruitment.breadcrumbs.dashboard'), 'url' => url('/admin')],
                ['label' => trans('recruitment.breadcrumbs.job_posts'), 'url' => route('JobPosts.index')],
                ['label' => trans('recruitment.breadcrumbs.add_job_post')],
            ],
        ]);
    }

    public function store(StoreJobPostRequest $request)
    {
        $this->authorize('create', JobPost::class);
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId() ?: (int) ($validated['school_id'] ?? 0);
        if (!$schoolId) {
            return back()->withErrors(['error' => trans('recruitment.messages.school_required')])->withInput();
        }

        $title = trim((string) $validated['title']);
        $baseSlug = Str::slug(Str::limit($title, 100, ''));
        $slug = $baseSlug ?: 'job-post';
        $suffix = 1;
        while (JobPost::query()->where('school_id', $schoolId)->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }

        JobPost::query()->create([
            'school_id' => $schoolId,
            'slug' => $slug,
            'title' => $title,
            'description' => $validated['description'],
            'requirements' => $validated['requirements'] ?? null,
            'cover_image_path' => $request->hasFile('cover_image')
                ? $request->file('cover_image')->store('recruitment/job-posts', 'public')
                : null,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
            'closed_at' => $validated['closed_at'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        toastr()->success(trans('recruitment.messages.job_post_created'));

        return redirect()->route('JobPosts.index');
    }

    public function edit(JobPost $jobPost)
    {
        $this->authorize('update', $jobPost);

        $schoolId = $this->currentSchoolId();

        return view('admin.recruitment.job_posts.edit', [
            'jobPost' => $jobPost,
            'notify' => $this->notifications(),
            'canPickSchool' => !$schoolId,
            'schools' => !$schoolId
                ? School::query()->select(['id', 'name_school'])->orderBy('name_school')->get()
                : collect(),
            'breadcrumbs' => [
                ['label' => trans('recruitment.breadcrumbs.dashboard'), 'url' => url('/admin')],
                ['label' => trans('recruitment.breadcrumbs.job_posts'), 'url' => route('JobPosts.index')],
                ['label' => trans('recruitment.breadcrumbs.edit_job_post')],
            ],
        ]);
    }

    public function update(UpdateJobPostRequest $request, JobPost $jobPost)
    {
        $this->authorize('update', $jobPost);
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId() ?: (int) ($validated['school_id'] ?? $jobPost->school_id);
        $coverImagePath = $jobPost->cover_image_path;

        if ($request->boolean('remove_cover_image') && $coverImagePath) {
            Storage::disk('public')->delete($coverImagePath);
            $coverImagePath = null;
        }

        if ($request->hasFile('cover_image')) {
            if ($coverImagePath) {
                Storage::disk('public')->delete($coverImagePath);
            }
            $coverImagePath = $request->file('cover_image')->store('recruitment/job-posts', 'public');
        }

        $jobPost->update([
            'school_id' => $schoolId,
            'title' => trim((string) $validated['title']),
            'description' => $validated['description'],
            'requirements' => $validated['requirements'] ?? null,
            'cover_image_path' => $coverImagePath,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
            'closed_at' => $validated['closed_at'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        toastr()->success(trans('recruitment.messages.job_post_updated'));

        return redirect()->route('JobPosts.index');
    }

    public function destroy(DestroyJobPostRequest $request)
    {
        $validated = $request->validated();
        $jobPost = JobPost::query()->findOrFail((int) $validated['id']);
        $this->authorize('delete', $jobPost);

        if ($jobPost->cover_image_path) {
            Storage::disk('public')->delete($jobPost->cover_image_path);
        }
        $jobPost->delete();

        toastr()->error(trans('recruitment.messages.job_post_deleted'));

        return redirect()->route('JobPosts.index');
    }
}
