<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateJobApplicationStatusRequest;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\JobPost;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin', 'force.password.change']);
    }

    public function index()
    {
        $this->authorize('viewAny', JobApplication::class);

        $schoolId = $this->currentSchoolId();
        $status = request('status');
        $search = request('q');
        $postId = request('post_id');

        $applications = JobApplication::query()
            ->forSchool($schoolId)
            ->select([
                'id',
                'job_post_id',
                'full_name',
                'phone',
                'email',
                'status',
                'created_at',
            ])
            ->with('post:id,title')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($postId, fn ($query) => $query->where('job_post_id', $postId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $posts = JobPost::query()
            ->forSchool($schoolId)
            ->orderByDesc('created_at')
            ->get(['id', 'title']);

        return view('admin.recruitment.applications.index', [
            'applications' => $applications,
            'posts' => $posts,
            'notify' => $this->notifications(),
        ]);
    }

    public function updateStatus(UpdateJobApplicationStatusRequest $request, JobApplication $jobApplication)
    {
        $this->authorize('update', $jobApplication);
        $validated = $request->validated();

        $jobApplication->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        toastr()->success('تم تحديث حالة الطلب');

        return redirect()->route('recruitment.applications.index');
    }

    public function downloadCv(JobApplication $jobApplication)
    {
        $this->authorize('downloadCv', $jobApplication);

        if (!Storage::disk('local')->exists($jobApplication->cv_path)) {
            abort(404);
        }

        return response()->download(
            storage_path('app/' . $jobApplication->cv_path),
            $jobApplication->cv_original_name ?? basename($jobApplication->cv_path),
            ['X-Content-Type-Options' => 'nosniff']
        );
    }
}
