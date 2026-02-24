@extends('layouts.masterhome')
@section('css')

@section('title')
   {{ trans('about.abouttitle') }}
@stop
@endsection

@section('content')
<!---page Title --->
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="text-center">						
                    <h2 class="page-title text-white">{{ trans('contact.contacnos') }}</h2>
                    <ol class="breadcrumb bg-transparent justify-content-center">
                        <li class="breadcrumb-item"><a href="#" class="text-white-50"><i class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">{{ trans('contact.contacnos') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>





<section class="py-50 text-center">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-12">
                <a href="#" data-bs-target="#modal-Ourphilosophy" data-bs-toggle="modal" class="pull-up">
                    <div class="p-10">
                        <span class="fs-40 icon-Compiling"><span class="path1"></span><span class="path2"></span></span>
                        <h3 class="my-15">{{ trans('about.Ourphilosophy') }} </h3>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <a href="#" data-bs-target="#modal-Ourmission" data-bs-toggle="modal" class="pull-up">
                    <div class="p-10">
                        <span class="fs-40 icon-Position1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span>
                        <h3 class="my-15">{{ trans('about.Ourmission') }}</h3>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <a href="#" data-bs-target="#modal-Ourvission" data-bs-toggle="modal" class="pull-up">
                    <div class="p-10">
                        <span class="fs-40 icon-Book-open"><span class="path1"></span><span class="path2"></span></span>
                        <h3 class="my-15">{{ trans('about.Ourvission') }}</h3>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <a href="#" data-bs-target="#modal-Keyofsuccess" data-bs-toggle="modal" class="pull-up">
                    <div class="p-10">
                        <span class="fs-40 icon-Road-Cone"><span class="path1"></span><span class="path2"></span></span>
                        <h3 class="my-15">{{ trans('about.Keyofsuccess') }}</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

   <!-- Notre Philosophie --> 
    <div class="modal center-modal fade text-center" id="modal-Ourphilosophy" tabindex="-1">
     <div class="modal-dialog">
      <div class="modal-content">
       {{-- <div class="modal-header">
          <h4 class="box-title text-info mb-0"> {{ trans('about.Ourphilosophy') }} </h4>
       </div> --}}
       <div class="modal-body">
           <h4>{{ trans('about.Ourphilosophybody') }}</h4>
       </div>
       <div class="modal-footer modal-footer-uniform">
        <a type="button" class="btn btn-info" data-bs-dismiss="modal">{{ trans('home.close') }}</a>
    
       </div>
      </div>
      </div>
    </div>

    <!--  Our mission -->
    <div class="modal center-modal fade text-center" id="modal-Ourmission" tabindex="-1">
        <div class="modal-dialog">
         <div class="modal-content">
          {{-- <div class="modal-header">
             <h4 class="box-title text-info mb-0"> {{ trans('about.Ourmission') }} </h4>
          </div> --}}
          <div class="modal-body">
              <h4>{{ trans('about.Ourmissionbody') }} </h4>
          </div>
          <div class="modal-footer modal-footer-uniform">
           <a type="button" class="btn btn-info" data-bs-dismiss="modal">{{ trans('home.close') }}</a>
       
          </div>
         </div>
         </div>
    </div>


    <!--  Our vission -->
    <div class="modal center-modal fade text-center" id="modal-Ourvission" tabindex="-1">
        <div class="modal-dialog">
         <div class="modal-content">
          {{-- <div class="modal-header">

             <h4 class="box-title text-info text-center "> {{ trans('about.Ourvission') }} </h4>
          </div> --}}
          <div class="modal-body">
              <h4>{{ trans('about.Ourvissionbody') }}</h4>
          </div>
          <div class="modal-footer modal-footer-uniform">
           <a type="button" class="btn btn-info" data-bs-dismiss="modal">{{ trans('home.close') }}</a>
       
          </div>
         </div>
         </div>
    </div>


       <!-- Key of success -->
    <div class="modal center-modal fade text-center" id="modal-Keyofsuccess" tabindex="-1">
        <div class="modal-dialog">
         <div class="modal-content">
          {{-- <div class="modal-header">
             <h4 class="box-title text-info mb-0"> {{ trans('about.Keyofsuccess') }} </h4>
          </div> --}}
          <div class="modal-body">
              <h4>{{ trans('about.Keyofsuccessbody') }} </h4>
          </div>
          <div class="modal-footer modal-footer-uniform">
           <a type="button" class="btn btn-info" data-bs-dismiss="modal">{{ trans('home.close') }}</a>
       
          </div>
         </div>
         </div>
    </div>



<section class="py-50 bg-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-12">
                <h2 class="mb-10">{{ trans('about.schoolname') }}</h2>
                <h4>
                    {{ trans('about.about') }}
                </h4>
                <a href="{{ LaravelLocalization::localizeUrl('/contact') }}" class="btn btn-info">{{ trans('contact.contacnos') }}</a>
            </div>
            <div class="col-lg-6 col-12 position-relative text-center">
                <div class="popup-vdo mt-30 mt-md-0">
                    <img src="{{ asset('images/logo512.png') }}" style="width: 90%; height: 90%;" class="img-fluid rounded" alt="">
                    <a href="https://www.youtube.com/watch?v=dbeUdU5nF88" class="popup-youtube play-vdo-bt waves-effect waves-circle btn btn-circle btn-info btn-lg"><i class="mdi mdi-play"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="py-30 bg-img countnm-bx" data-jarallax='{"speed": 0.4}' style="background-image: url({{ asset('images/grade.jpg') }})" data-overlay="5">
    <div class="container">			
        <div class="box box-body bg-transparent mb-0">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="text-center mb-30 mb-lg-0">
                        <div class="w-80 h-80 l-h-100 rounded-circle b-1 border-white text-center mx-auto">
                            <span class="text-white fs-40 icon-User"><span class="path1"></span><span class="path2"></span></span>
                        </div>
                        <h1 class="countnm my-10 text-white">40</h1>
                        <div class="text-uppercase text-white">{{ trans('home.teacher') }}</div>
                    </div>
                </div>	
                <div class="col-lg-3 col-6">
                    <div class="text-center mb-30 mb-lg-0">
                        <div class="w-80 h-80 l-h-100 rounded-circle b-1 border-white text-center mx-auto">
                            <span class="text-white fs-40 icon-Book"></span>
                        </div>
                        <h1 class="countnm my-10 text-white">120</h1>
                        <div class="text-uppercase text-white">{{ trans('home.courses') }}</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="text-center">
                        <div class="w-80 h-80 l-h-100 rounded-circle b-1 border-white text-center mx-auto">
                            <span class="text-white fs-40 icon-Group"><span class="path1"></span><span class="path2"></span></span>
                        </div>
                        <h1 class="countnm my-10 text-white">7485</h1>
                        <div class="text-uppercase text-white">{{ trans('home.student') }}</div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="text-center">
                        <div class="w-80 h-80 l-h-100 rounded-circle b-1 border-white text-center mx-auto">
                            <span class="text-white fs-40 icon-Globe"><span class="path1"></span><span class="path2"></span></span>
                        </div>
                        <h1 class="countnm my-10 text-white">2</h1>
                        <div class="text-uppercase text-white">{{ trans('home.city') }}</div>
                    </div>
                </div>			
            </div>
        </div>
    </div>
</section>


<section class="py-50">
    <div class="container">		
        <div class="row">
            <div class="col-12">
                <div class="owl-carousel owl-theme owl-btn-1" data-nav-arrow="true" data-nav-dots="false" data-items="1" data-md-items="1" data-sm-items="1" data-xs-items="1" data-xx-items="1">
                    
                    <div class="item">
                        <div class="text-center">
                            <div class="bg-primary-light w-50 mx-auto rounded-circle overflow-hidden">
                                <img src="{{ asset('images/bachir.jpg') }}" class="avatar-lg w-auto" alt="">
                            </div>
                            <div class="max-w-750 mx-auto">									
                                <div class="testimonial-info">
                                    <h4 class="name mb-0 mt-10">بشير طهراوي</h4>
                                    <p>{{ trans('home.teacher') }}</p>
                                </div>
                                <div class="testimonial-content">
                                    <ul class="cours-star">
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li class="active"><i class="fa fa-star"></i></li>
                                    </ul>
                                    <p class="fs-16">
                                        اشهد للأستاذ منير التجاني "مدير مدرسة سبل النجاح الحالي" بانه رجل فاضل وطيب وصاحب همة عالية لا تفارق محياه الابتسامة ورجل مثقف وهوأخ وصديق عزيز وهو من معدن أصيل وطيب ويتميز بالجدية والكفاءة والإخلاص والتفاني في العمل . 
                                        ادعو له المولى عز وجل أن يمده بالصحة والعافية وان يحفظ له الأهل وان يرفع مقامه في الدنيا والآخرة وان ويرزقه من حيث لا يحتسب                     
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="text-center">
                            <div class="bg-primary-light w-50 mx-auto rounded-circle overflow-hidden">
                                <img src="{{ asset('images/zakaria.jpg') }}" class="avatar-lg w-auto" alt="">
                            </div>
                            <div class="max-w-750 mx-auto">									
                                <div class="testimonial-info">
                                    <h4 class="name mb-0 mt-10">Zakaria Almor</h4>
                                    <p>-تلميذ</p>
                                </div>
                                <div class="testimonial-content">
                                    <ul class="cours-star">
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li class="active"><i class="fa fa-star"></i></li>
                                        <li><i class="fa fa-star"></i></li>
                                    </ul>
                                    <p class="fs-16">
                                        مدرستي واحلى مدرسة النجاح ولي قراوني فيها من موديرها حتا للمراقبين لي فيها اطيب الناس ونعمة ناس ربي يحفضهم ويوفقهم
                                    </p>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>	
            </div>
        </div>
    </div>
</section>


<section class="py-100" data-jarallax='{"speed": 0.4}' style="background-image: url({{ asset('images/library.jpg') }});" data-overlay="5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-12">				
                <div class="text-center text-white">
                    <h2 class="mb-15 fw-600 fs-40">{{ trans('home.motecole') }}</h2>
                    <h4>{{ trans('about.schoolname') }}</h4>
                    <p>
                        {{ trans('about.welcome') }}
                    </p>

                    <div class="mt-5"><a href="#" class="btn btn-primary">{{ trans('home.readmore') }}</a></div>
                </div>
            </div>
        </div>
    </div>
</section>
<br><br>
<br>


{{-- <section class="py-50">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>{{ trans('about.team') }}</h2>
                <hr>
            </div>
        </div>			
        <div class="row">				
          <div class="col-12 col-lg-3 col-md-6">
            <div class="box">
              <div class="box-header no-border p-0">				
                <a href="#">
                  <img class="img-fluid" src="../images/front-end-img/avatar/375x200/1.jpg" alt="">
                </a>
              </div>
              <div class="box-body">
                  <div class="text-center">
                    <div class="user-contact list-inline text-center">
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-facebook"><i class="fa fa-facebook"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-instagram"><i class="fa fa-instagram"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-twitter"><i class="fa fa-twitter"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-warning"><i class="fa fa-envelope"></i></a>				
                    </div>
                    <h4 class="my-10"><a href="#">Tristan</a></h4>
                    <h6 class="user-info mt-0 mb-10 text-fade">Web Designer</h6>
                    <p class="text-fade w-p85 mx-auto">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                  </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-3 col-md-6">
            <div class="box">
              <div class="box-header no-border p-0">				
                <a href="#">
                  <img class="img-fluid" src="../images/front-end-img/avatar/375x200/8.jpg" alt="">
                </a>
              </div>
              <div class="box-body">
                  <div class="text-center">
                    <div class="user-contact list-inline text-center">
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-facebook"><i class="fa fa-facebook"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-instagram"><i class="fa fa-instagram"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-twitter"><i class="fa fa-twitter"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-warning"><i class="fa fa-envelope"></i></a>					
                    </div>
                    <h4 class="my-10"><a href="#">Michael</a></h4>
                    <h6 class="user-info mt-0 mb-10 text-fade">Art Director</h6>
                    <p class="text-fade w-p85 mx-auto">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                  </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-3 col-md-6">
            <div class="box">
              <div class="box-header no-border p-0">				
                <a href="#">
                  <img class="img-fluid" src="../images/front-end-img/avatar/375x200/2.jpg" alt="">
                </a>
              </div>
              <div class="box-body">
                  <div class="text-center">
                    <div class="user-contact list-inline text-center">
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-facebook"><i class="fa fa-facebook"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-instagram"><i class="fa fa-instagram"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-twitter"><i class="fa fa-twitter"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-warning"><i class="fa fa-envelope"></i></a>					
                    </div>
                    <h4 class="my-10"><a href="#">Sophia</a></h4>
                    <h6 class="user-info mt-0 mb-10 text-fade">Programar</h6>
                    <p class="text-fade w-p85 mx-auto">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                  </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-3 col-md-6">
            <div class="box">
              <div class="box-header no-border p-0">				
                <a href="#">
                  <img class="img-fluid" src="../images/front-end-img/avatar/375x200/4.jpg" alt="">
                </a>
              </div>
              <div class="box-body">
                  <div class="text-center">
                    <div class="user-contact list-inline text-center">
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-facebook"><i class="fa fa-facebook"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-instagram"><i class="fa fa-instagram"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-twitter"><i class="fa fa-twitter"></i></a>
                        <a href="#" class="btn btn-circle mb-5 btn-xs btn-warning"><i class="fa fa-envelope"></i></a>					
                    </div>
                    <h4 class="my-10"><a href="#">Johen Doe</a></h4>
                    <h6 class="user-info mt-0 mb-10 text-fade">Philosophy</h6>
                    <p class="text-fade w-p85 mx-auto">Lorem Ipsum is simply dummy text of the printing and typesetting industry. </p>
                  </div>
              </div>
            </div>
          </div>
        </div>
    </div>
</section> --}}
@endsection


@section('js')
    
@endsection