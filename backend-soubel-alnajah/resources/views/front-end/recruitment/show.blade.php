@extends('layouts.masterhome')
@section('title', $jobPost->title)

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="text-center">
            <h2 class="page-title text-white">{{ $jobPost->title }}</h2>
        </div>
    </div>
</section>

<section class="py-50">
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            @foreach($errors->all() as $error)
                <div class="alert alert-danger">{{ $error }}</div>
            @endforeach
        @endif

        <div class="row">
            <div class="col-md-7">
                <div class="box">
                    <div class="box-body">
                        <h4>تفاصيل الإعلان</h4>
                        <div class="mb-3">{!! nl2br(e($jobPost->description)) !!}</div>
                        @if($jobPost->requirements)
                            <h5>الشروط</h5>
                            <div>{!! nl2br(e($jobPost->requirements)) !!}</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="box">
                    <div class="box-body">
                        <h4>استمارة الترشح</h4>
                        <form method="POST" action="{{ route('public.jobs.apply', $jobPost) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">الاسم واللقب</label>
                                <input type="text" name="full_name" class="form-control" value="{{ old('full_name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني (اختياري)</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">السيرة الذاتية (PDF/DOC/DOCX)</label>
                                <input type="file" name="cv" class="form-control" required>
                            </div>
                            <input type="text" name="website" value="" style="display:none" tabindex="-1" autocomplete="off">
                            <button class="btn btn-primary">إرسال</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
