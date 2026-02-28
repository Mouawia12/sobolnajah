@extends('layouts.masterhome')

@section('title')
   {{ __('دخول المحاسب المالي') }}
@stop

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="text-center">
            <h2 class="page-title text-white">{{ __('دخول المحاسب المالي') }}</h2>
        </div>
    </div>
</section>

<section class="py-50">
    <div class="container">
        <div class="row justify-content-center g-0">
            <div class="col-lg-5 col-md-6 col-12">
                <div class="box box-body">
                    <div class="content-top-agile pb-0 pt-20">
                        <h2 class="text-info">{{ __('بوابة المحاسب') }}</h2>
                        <p class="mb-0">{{ __('سجّل الدخول بحساب المحاسب المالي') }}</p>
                    </div>
                    <div class="p-40">
                        <form action="{{ route('accountant.login.submit') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <div class="input-group mb-15">
                                    <span class="input-group-text bg-transparent"><i class="ti-email"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ trans('login.email') }}" required autofocus>
                                </div>
                                @error('email')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <div class="input-group mb-15">
                                    <span class="input-group-text bg-transparent"><i class="ti-lock"></i></span>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ trans('login.password') }}" required>
                                </div>
                                @error('password')<span class="text-danger small">{{ $message }}</span>@enderror
                            </div>

                            <button type="submit" class="btn btn-info w-p100 mt-15">{{ __('دخول المحاسب') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
