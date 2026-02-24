@extends('layouts.masterhome')
@section('css')

@section('title')
   {{ trans('home.welcome') }}
@stop
@endsection

@section('content')

<section class="bg-img pt-130 pb-50" data-overlay-light="1" style="background-image: url({{ asset('images/cover2.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center mt-80">
                    <div class="logo text-center">
                        <a href="/" class="brand d-lg-block d-none">
                          <img style="width: 15%;height: 15%;background-color: white;border-radius: 50%;" src="{{ asset('images/logo.png')}}" alt=""/>
                          {{-- <img style="background-color: white;border-radius: 20%;" src="{{ asset('images/huitnet-logo.png')}}" alt=""/> --}}

                      </a>
                    </div>
                </div> <br>
                <form class="cours-search bg-black-40 mb-30">
                    <div class="input-group">
                        <input type="text" class="form-control text-center"  placeholder= "{{ trans('home.wdyw') }}">
                        {{-- <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Search</button>
                        </div> --}}
                    </div>
                </form>
                {{-- <div class="text-center">
                    <a href="courses_list.html" class="btn btn-dark">Browse Courses List</a>
                </div> --}}
            </div>
        </div>
    </div>

</section>

@include('layouts.homebody')

@endsection
