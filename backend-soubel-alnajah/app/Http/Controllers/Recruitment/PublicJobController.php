<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\JobPost;
use Illuminate\Support\Str;

class PublicJobController extends Controller
{
    public function index()
    {
        $jobPosts = JobPost::query()
            ->published()
            ->withCount('applications')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('front-end.recruitment.jobs', [
            'jobPosts' => $jobPosts,
        ]);
    }

    public function show(JobPost $jobPost)
    {
        if ($jobPost->status !== 'published') {
            abort(404);
        }

        return view('front-end.recruitment.show', [
            'jobPost' => $jobPost,
        ]);
    }

    public function apply(StoreJobApplicationRequest $request, JobPost $jobPost)
    {
        if ($jobPost->status !== 'published') {
            abort(404);
        }

        $validated = $request->validated();

        $cv = $request->file('cv');
        $filename = now()->format('YmdHis') . '_' . Str::uuid() . '.' . $cv->getClientOriginalExtension();
        $path = $cv->storeAs('private/recruitment/' . $jobPost->school_id . '/' . $jobPost->id, $filename, 'local');

        JobApplication::query()->create([
            'school_id' => $jobPost->school_id,
            'job_post_id' => $jobPost->id,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'status' => 'new',
            'cv_path' => $path,
            'cv_original_name' => $cv->getClientOriginalName(),
            'cv_mime' => $cv->getClientMimeType(),
            'cv_size' => $cv->getSize(),
            'submitted_ip' => (string) $request->ip(),
            'submitted_user_agent' => (string) $request->userAgent(),
        ]);

        return back()->withSuccess(trans('recruitment.messages.application_submitted'));
    }
}
