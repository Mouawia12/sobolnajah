@extends('layouts.masterhome')
@section('css')

@section('title')
   login
@stop
@endsection

@section('content')

<!---page Title --->
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center">						
                    <h2 class="page-title text-white">{{ trans('login.login') }}</h2>
                    <ol class="breadcrumb bg-transparent justify-content-center">
                        <li class="breadcrumb-item"><a href="#" class="text-white-50"><i class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">{{ trans('login.login') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>
<!--Page content -->

<section class="py-50">
    <div class="container">
        <div class="row justify-content-center g-0">
            <div class="col-lg-5 col-md-5 col-12">
                <div class="box box-body">
                    <div class="content-top-agile pb-0 pt-20">
                        <h2 class="text-info">{{ trans('login.sobolnajah') }}</h2>
                        <p class="mb-0">{{ trans('login.login') }}</p>							
                    </div>
                    <div class="p-40">
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <div class="input-group mb-15">
                                    <span class="input-group-text bg-transparent"><i class="ti-email"></i></span>
                                    <input id="email" type="email" placeholder="Email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-15">
                                    <span class="input-group-text  bg-transparent"><i class="ti-lock"></i></span>
                                    <input id="password" type="password" placeholder="Modepasse" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                </div>
                            </div>
                              <div class="row">
                                {{-- <div class="col-6">
                                  <div class="checkbox ms-5">
                                    <input type="checkbox" name="remember" id="basic_checkbox_1" {{ old('remember') ? 'checked' : '' }}>
                                    <label for="basic_checkbox_1" class="form-label">Remember Me</label>
                                  </div>
                                </div>
                                <!-- /.col -->
                                <div class="col-6">
                                 <div class="fog-pwd text-end">
                                    @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="hover-warning"><i class="ion ion-locked"></i> Forgot pwd?</a>
                                    @endif
                                    <br>
                                  </div>
                                </div> --}}
                                <!-- /.col -->
                                <div class="col-12 text-center">
                                  <button type="submit" class="btn btn-info w-p100 mt-15">{{ trans('login.login') }}</button>
                                </div>
                                <!-- /.col -->
                              </div>
                        </form>	
                        <div class="text-center">
                            <p class="mt-15 mb-0">{{ trans('login.dhaccount') }} <a href="{{ route('register') }}" class="text-warning ms-5">Register</a></p>
                        </div>	
                    </div>
                </div>								

                {{-- <div class="text-center">
                  <p class="mt-20">- Login With -</p>
                  <p class="d-flex gap-items-2 mb-0 justify-content-center">
                      <a class="btn btn-social-icon btn-round btn-facebook" href="#"><i class="fa fa-facebook"></i></a>
                      <a class="btn btn-social-icon btn-round btn-twitter" href="#"><i class="fa fa-twitter"></i></a>
                      <a class="btn btn-social-icon btn-round btn-instagram" href="#"><i class="fa fa-instagram"></i></a>
                    </p>	
                </div> --}}
            </div>
        </div>
    </div>
</section>
@endsection


