@extends('layouts.masterhome')
@section('css')

@section('title')
   home
@stop
@endsection

@section('content')

<section class="bg-img pt-130 pb-50" data-overlay-light="9" style="background-image: url({{ asset('images/cover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center mt-80">
                    <h1 class="box-title text-dark mb-30">Find Your Online Course</h1>	
                </div>
                <form class="cours-search bg-black-40 mb-30">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="What do you want to learn today?">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Search</button> 
                        </div>
                    </div>
                </form>	
                <div class="text-center">
                    <a href="courses_list.html" class="btn btn-dark">Browse Courses List</a>
                </div>
            </div>
        </div>
    </div>

</section>

@include('layouts.homebody')




{{-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div> --}}
@endsection
