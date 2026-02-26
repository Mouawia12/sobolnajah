@extends('layouts.masterhome')
@section('title', 'إعلانات التوظيف')

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="text-center">
            <h2 class="page-title text-white">إعلانات التوظيف</h2>
        </div>
    </div>
</section>

<section class="py-50">
    <div class="container">
        <div class="row">
            @forelse($jobPosts as $jobPost)
                <div class="col-md-6 mb-3">
                    <div class="box">
                        <div class="box-body">
                            <h4>{{ $jobPost->title }}</h4>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($jobPost->description), 180) }}</p>
                            <a href="{{ route('public.jobs.show', $jobPost) }}" class="btn btn-primary">عرض والتقديم</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">لا توجد إعلانات توظيف متاحة حاليًا.</div>
                </div>
            @endforelse
        </div>
        {{ $jobPosts->links() }}
    </div>
</section>
@endsection
