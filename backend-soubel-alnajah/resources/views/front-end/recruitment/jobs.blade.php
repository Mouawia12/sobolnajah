@extends('layouts.masterhome')
@section('title', trans('recruitment.public.page_title'))
@section('css')
<style>
    .jobs-page {
        --jobs-primary: #0b4f9f;
        --jobs-accent: #1fb6ff;
        --jobs-ink: #12355b;
        --jobs-soft: #f4f8ff;
    }

    .jobs-hero {
        position: relative;
        overflow: hidden;
    }

    .jobs-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 12% 20%, rgba(31, 182, 255, 0.4), transparent 40%),
            radial-gradient(circle at 88% 30%, rgba(255, 255, 255, 0.2), transparent 35%);
        pointer-events: none;
    }

    .jobs-subtitle {
        color: #f5f9ff;
        opacity: 0.95;
        font-size: 16px;
        margin-top: 10px;
    }

    .jobs-grid-wrap {
        background: linear-gradient(180deg, var(--jobs-soft) 0%, #ffffff 70%);
        border-radius: 18px;
        padding: 26px 20px;
    }

    .job-card {
        border: 0;
        border-radius: 16px;
        height: 100%;
        background: #fff;
        box-shadow: 0 10px 30px rgba(10, 45, 90, 0.08);
        transition: transform .25s ease, box-shadow .25s ease;
        opacity: 0;
        transform: translateY(18px);
        animation: jobsFadeUp .45s ease forwards;
    }

    .job-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 36px rgba(10, 45, 90, 0.15);
    }

    .job-card .box-body {
        padding: 22px;
        display: flex;
        flex-direction: column;
        height: 100%;
        align-items: center;
        text-align: center;
    }

    .job-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 12px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 700;
        background: #e8f8ee;
        color: #198754;
        margin-bottom: 14px;
    }

    .job-status::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #1cb35d;
    }

    .job-title {
        color: var(--jobs-ink);
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 10px;
        line-height: 1.5;
    }

    .job-snippet {
        color: #516882;
        line-height: 1.9;
        min-height: 90px;
        margin-bottom: 14px;
    }

    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
        justify-content: center;
    }

    .job-meta-chip {
        border-radius: 100px;
        padding: 6px 12px;
        background: #eef4fc;
        color: #365b84;
        font-size: 12px;
        font-weight: 600;
    }

    .job-cta {
        margin-top: auto;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border: 0;
        border-radius: 12px;
        padding: 11px 18px;
        font-weight: 700;
        background: linear-gradient(135deg, var(--jobs-primary) 0%, var(--jobs-accent) 100%);
        color: #fff;
        text-decoration: none;
    }

    .job-cta:hover {
        color: #fff;
        filter: brightness(1.03);
    }

    .jobs-empty {
        background: linear-gradient(135deg, #0b4f9f 0%, #1fb6ff 100%);
        color: #fff;
        border: 0;
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        font-size: 18px;
        font-weight: 600;
    }

    .jobs-pagination {
        margin-top: 22px;
    }

    .jobs-pagination nav {
        display: flex;
        justify-content: center;
    }

    @keyframes jobsFadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<section class="bg-img pt-150 pb-20 jobs-hero" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="text-center">
            <h2 class="page-title text-white">{{ trans('recruitment.public.page_title') }}</h2>
            <p class="jobs-subtitle">{{ trans('recruitment.public.subtitle') }}</p>
        </div>
    </div>
</section>

<section class="py-50 jobs-page">
    <div class="container">
        <div class="jobs-grid-wrap">
        <div class="row">
            @if($jobPosts->count() > 0)
                @foreach($jobPosts as $jobPost)
                    <div class="col-md-6 mb-3">
                        <div class="box job-card" style="animation-delay: {{ $loop->index * 90 }}ms;">
                            <div class="box-body">
                                <span class="job-status">{{ trans('recruitment.public.available_now') }}</span>
                                <h4 class="job-title">{{ $jobPost->title }}</h4>
                                <p class="job-snippet">{{ \Illuminate\Support\Str::limit(strip_tags($jobPost->description), 180) }}</p>
                                <div class="job-meta">
                                    <span class="job-meta-chip">{{ trans('recruitment.public.published_at') }}: {{ optional($jobPost->published_at)->format('Y-m-d') ?? '-' }}</span>
                                    <span class="job-meta-chip">{{ trans('recruitment.public.closed_at') }}: {{ optional($jobPost->closed_at)->format('Y-m-d') ?? trans('recruitment.public.open_ended') }}</span>
                                    <span class="job-meta-chip">{{ trans('recruitment.public.applications_count') }}: {{ $jobPost->applications_count }}</span>
                                </div>
                                <a href="{{ route('public.jobs.show', $jobPost) }}" class="job-cta">{{ trans('recruitment.public.view_apply') }}</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12">
                    <div class="jobs-empty">{{ trans('recruitment.public.empty') }}</div>
                </div>
            @endif
        </div>
        <div class="jobs-pagination">
            {{ $jobPosts->links() }}
        </div>
        </div>
    </div>
</section>
@endsection
