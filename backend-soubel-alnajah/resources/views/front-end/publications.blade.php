@extends('layouts.masterhome')
@section('css')

{{-- @livewireStyles --}}

@section('title')
   {{ trans('main_header.agendascolaire') }}
@stop
@endsection

@section('content')

<!---page Title --->
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center">						
                    <h2 class="page-title text-white">{{ trans('main_header.agendascolaire') }}</h2>
                    <ol class="breadcrumb bg-transparent justify-content-center">
                        <li class="breadcrumb-item"><a href="#" class="text-white-50"><i class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">{{ trans('main_header.agendascolaire') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

            
         

<section class="py-50">
    <div class="container">
    


                {{-- <livewire:posts />  --}}
                @livewire('posts')

               
           
     		
    </div>
</section>
@endsection


