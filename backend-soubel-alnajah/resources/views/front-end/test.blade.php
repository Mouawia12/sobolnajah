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

            
         

            
{{-- @for ($i = $total-1; $i >= 0; $i--)
              {{    $Publicationss[$i]->title; }} 
              <br>
               @endfor  --}}
             

<!--Page content -->

<section class="py-50">
    <div class="container">
    


                
 {{-- <div > --}}

   
{{-- <div>
    <input wire:model="search" type="text" placeholder="Search users..."/>
 
    <ul>
        @foreach($users as $user)
            <li>{{ $user->getTranslation('name_grades', 'fr') }}</li>
        @endforeach
    </ul>
</div> --}}



<div class="row">

   {{ csrf_field() }}
   <div class="col-lg-9 col-md-8 col-12" id="categoryData">
    
     
      
       

       </div>   
  
       <div class="col-lg-3 col-md-4 col-12">
           <div class="side-block px-20 py-10 bg-white position-sticky t-100">
               <div class="widget courses-search-bx placeholdertx mb-10">
                   <div class="form-group">
                       <div class="input-group">
                           <label class="form-label">{{ trans('inscription.search') }} </label>
                           <input name="name" type="text" required="" class="form-control">
                       </div>
                   </div>
               </div>	
               <div class="widget clearfix">
                   <h4 class="pb-15 mb-15 bb-1">{{ trans('pub.grade') }}       <span class="mx-0 badge badge-info-light">{{$Publications->count()}}</span>
                   </h4>
                   
                   <ul class="list list-unstyled">
                       <li><a  href="#" wire:click="allpub()"><i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i> {{ trans('pub.all') }} </a></li>

                       @foreach ($Grade as $g)
                               
                           <li><a href="#" wire:click="grade({{$g->id}})"><i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i>{{$g->name_grades}}</a></li>
   
                       @endforeach
   
                   </ul>
               </div>
               
               <div class="widget clearfix">
                   <h4 class="pb-15 mb-25 bb-1">{{ trans('pub.agenda') }}       <span class="mx-0 badge badge-danger-light">{{$Agenda->count()}}</span></h4>
                   <ul class="list list-unstyled">
                       <li><a  href="#" wire:click="allpub()"><i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i> {{ trans('pub.all') }} </a></li>

                       @foreach ($Agenda as $a)
                       <li><a href="#" wire:click="agenda({{$a->id}})"><i class="fa fa-angle-double-{{ trans('pub.lang') }}"></i> {{$a->name_agenda}}</a></li>
                       @endforeach
   
                   </ul>
               </div>
               {{-- <div class="widget clearfix">
                   <h4 class="pb-15 mb-25 bb-1">Archives</h4>
                   <ul class="list list-unstyled">
                       <li><a href="#"><i class="fa fa-angle-double-right"></i> November 2020</a></li>
                       <li><a href="#"><i class="fa fa-angle-double-right"></i> October 2020</a></li>
                       <li><a href="#"><i class="fa fa-angle-double-right"></i> September 2020</a></li>
                       <li><a href="#"><i class="fa fa-angle-double-right"></i> August 2020</a></li>
                       <li><a href="#"><i class="fa fa-angle-double-right"></i> July 2020</a></li>
                   </ul>
               </div> --}}
             
               
               {{-- <div class="widget">
                   <h4 class="pb-15 mb-25 bb-1">Recent Posts </h4>
                  
               
                   
               <?php $i=0; ?>
          
               @for ($i ; $i <= 3; $i++)
                   @if ($i < $Publications->count())
                       <div class="recent-post clearfix">
                       <div class="recent-post-image">
                           <img class="img-fluid bg-primary-light" src="../images/front-end-img/courses/cor-logo-3.png" alt="">
                       </div>
                       <div class="recent-post-info">
                           <a href="#">{{    $Publications[$i]->title; }} </a>
                           <span><i class="fa fa-calendar-o"></i>{{ $Publications[$i]->created_at; }} </span>
                       </div>
                   </div>
                   @endif     
               @endfor 
                   
               </div> --}}
   
               <div class="widget mb-10">
                   <h4 class="pb-15 mb-25 bb-1">{{ trans('contact.Getintouch') }}</h4>
                   <form class="gray-form">
                       <div class="form-group">
                           <input type="email" class="form-control" id="exampleInputEmail1" placeholder="{{ trans('contact.firstname') }} ">
                       </div>
                       <div class="form-group">
                           <input type="email" class="form-control" id="exampleInputphone" placeholder="{{ trans('contact.email') }}">
                       </div>
   
                       <div class="form-group">
                           <textarea class="form-control" rows="4" placeholder="{{ trans('contact.subject') }}"></textarea>
                       </div>
                       <a class="btn btn-primary w-p100" href="#">{{ trans('contact.sendmessage') }}</a>
                   </form>
               </div>	
           </div>
       </div>
</div>

       

        
   

               
           
     		
    </div>
</section>
@endsection


@section('js')
    <script>

            var _token = $('input[name="_token"]').val();

            load_more('', _token);

            function load_more(id="", _token)
            {
            $.ajax({
               url:"{{ route('Publications.load_more') }}",
               method:"POST",
               data:{id:id, _token:_token},
               success:function(data)
               {
               $('#loadMoreButton').remove();
               $('#categoryData').append(data);
               }
            })
            }

            $(document).on('click', '#load_more_button', function(){
            var id = $(this).data('id');
            $('#load_more_button').html('<b>Loading...</b>');
            load_more(id, _token);
            });


    </script>
@endsection