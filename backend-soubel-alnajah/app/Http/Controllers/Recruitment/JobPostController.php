<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobPostRequest;
use App\Http\Requests\UpdateJobPostRequest;
use App\Models\Recruitment\JobPost;
use App\Models\School\School;
use Illuminate\Support\Str;

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
            'schools' => !$schoolId ? School::query()->orderBy('name_school')->get() : collect(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'إعلانات التوظيف'],
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
            'schools' => !$schoolId ? School::query()->orderBy('name_school')->get() : collect(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'إعلانات التوظيف', 'url' => route('JobPosts.index')],
                ['label' => 'إضافة إعلان'],
            ],
        ]);
    }

    public function store(StoreJobPostRequest $request)
    {
        $this->authorize('create', JobPost::class);
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId() ?: (int) ($validated['school_id'] ?? 0);
        if (!$schoolId) {
            return back()->withErrors(['error' => 'School is required.'])->withInput();
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
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
            'closed_at' => $validated['closed_at'] ?? null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        toastr()->success('تم إنشاء إعلان التوظيف بنجاح');

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
            'schools' => !$schoolId ? School::query()->orderBy('name_school')->get() : collect(),
            'breadcrumbs' => [
                ['label' => 'لوحة التحكم', 'url' => url('/admin')],
                ['label' => 'إعلانات التوظيف', 'url' => route('JobPosts.index')],
                ['label' => 'تعديل إعلان'],
            ],
        ]);
    }

    public function update(UpdateJobPostRequest $request, JobPost $jobPost)
    {
        $this->authorize('update', $jobPost);
        $validated = $request->validated();

        $schoolId = $this->currentSchoolId() ?: (int) ($validated['school_id'] ?? $jobPost->school_id);

        $jobPost->update([
            'school_id' => $schoolId,
            'title' => trim((string) $validated['title']),
            'description' => $validated['description'],
            'requirements' => $validated['requirements'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['published_at'] ?? null,
            'closed_at' => $validated['closed_at'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        toastr()->success('تم تحديث إعلان التوظيف');

        return redirect()->route('JobPosts.index');
    }

    public function destroy(JobPost $jobPost)
    {
        $this->authorize('delete', $jobPost);

        $jobPost->delete();

        toastr()->error('تم حذف إعلان التوظيف');

        return redirect()->route('JobPosts.index');
    }
}
