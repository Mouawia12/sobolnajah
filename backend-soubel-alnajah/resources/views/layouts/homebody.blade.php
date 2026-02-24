<!--Page content -->	
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












<section class="py-xl-100 py-50 bg-white" data-aos="fade-up">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-12">
                <div class="box box-body p-xl-50 p-30 bg-lightest">
                    <div class="row align-items-center">
                        <div class="col-lg-6 col-12">
                            <p class="badge badge-info badge-lg">4</p>
                            <h1 class="mb-15">{{ trans('home.phases') }}</h1>
                            {{-- <h4 class="fw-400">{{ trans('home.clesucces') }}</h4> --}}
                            <p class="fs-22"> {{ trans('home.clesucces2') }} </p>
                            <a href="{{route('Publications.index')}}" class="btn btn-outline btn-info">{{ trans('home.readmore') }}</a>
                        </div>
                        <div class="col-lg-6 col-12 position-relative">
                            <div class="media-list media-list-hover media-list-divided md-post mt-lg-0 mt-30">
                                <a class="media media-single box-shadowed bg-white pull-up mb-15" href="#">
                                  <img class="w-80 rounded ms-0" src="{{ asset('images/logo512.png') }}" alt="...">
                                  <div class="media-body fw-500">
                                    <h5 class="overflow-hidden text-overflow-h nowrap">{{ trans('home.preparatoire') }}</h5>
                                    <small class="text-fade">2019</small>
                                    <p><small class="text-fade mt-10">{{ trans('home.sobolnajahrimal') }}</small></p>
                                  </div>
                                </a>

                                <a class="media media-single box-shadowed bg-white pull-up mb-15" href="#">
                                    <img class="w-80 rounded ms-0" src="{{ asset('images/logo512.png') }}" alt="...">
                                    <div class="media-body fw-500">
                                      <h5 class="overflow-hidden text-overflow-h nowrap">{{ trans('home.primaire') }}</h5>
                                      <small class="text-fade">2015</small>
                                      <p><small class="text-fade mt-10">{{ trans('home.sobolnajahrimal') }}</small></p>
                                    </div>
                                  </a>

                                  <a class="media media-single box-shadowed bg-white pull-up mb-15" href="#">
                                    <img class="w-80 rounded ms-0" src="{{ asset('images/logo512.png') }}" alt="...">
                                    <div class="media-body fw-500">
                                      <h5 class="overflow-hidden text-overflow-h nowrap">{{ trans('home.moyen') }}</h5>
                                      <small class="text-fade">2005</small>
                                      <p><small class="text-fade mt-10">{{ trans('home.sobolnajahrimal') }}</small></p>
                                    </div>
                                  </a>

                                  <a class="media media-single box-shadowed bg-white pull-up mb-15" href="#">
                                    <img class="w-80 rounded ms-0" src="{{ asset('images/logo512.png') }}" alt="...">
                                    <div class="media-body fw-500">
                                      <h5 class="overflow-hidden text-overflow-h nowrap">{{ trans('home.lycee') }}</h5>
                                      <small class="text-fade">2005</small>
                                      <p><small class="text-fade mt-10">{{ trans('home.sobolnajahrimal') }}</small></p>
                                    </div>
                                  </a>
                         
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

<section class="py-50" data-aos="fade-up">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-12 text-center">
                <h1 class="mb-15">{{ trans('home.lastpub') }}</h1>				
                <hr class="w-100 bg-primary">

            </div>
        </div>                              
        <br/><br/><br/><br/>               
                       

        <div class="row">
            @foreach ($Publication as $pub)

            <div class="col-xl-4 col-md-4 col-12">
                <div class="blog-post">
                    <div class="entry-image clearfix">
                        <div class="owl-carousel bottom-dots-center owl-theme" data-nav-dots="true" data-autoplay="true"  data-items="1" data-md-items="1" data-sm-items="1" data-xs-items="1" data-xx-items="1">  
                          
                           
                          
                            @foreach ($pub->galleries as $gal)
                                @foreach(json_decode($gal->img_url, true) as $images)

                                    <div class="item">
                                        <img src="{{ asset('storage/agenda/'.$images.'')}}" alt="">
                                    </div>
    
                                @endforeach 
                            @endforeach 
                            


                         
                        </div>
                    </div>
                    <div class="blog-detail">
                        <div class="entry-title mb-10">
                            <a href="#">{{ $pub->title}}</a>
                        </div>
                        <div class="entry-meta mb-10">
                            <ul class="list-unstyled">
                                {{-- <li><a href="#"><i class="fa fa-folder-open-o"></i> Design</a></li> --}}
                                <li><a href="#"><i class="fa fa-heart-o"></i>{{ $pub->like }}</a></li>
                                <li><a href="#"><i class="fa fa-calendar-o"></i> {{ $pub->created_at }}</a></li>
                            </ul>
                        </div>
                        <div class="entry-content overflow-hidden text-overflow-h nowrap">
                            {{ $pub->body}}
                        </div>
                        <div class="entry-share d-flex justify-content-between align-items-center">
                            <div class="entry-button">
                                <a href="{{route('Publications.index')}}" class="btn btn-primary btn-sm">{{ trans('home.readmore') }}</a>
                            </div>
                            <div class="social">
                                <strong>{{ trans('home.share') }} : </strong>
                                <ul class="list-unstyled">
                                    <li>
                                        <a href="#"> <i class="fa fa-facebook"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"> <i class="fa fa-twitter"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"> <i class="fa fa-pinterest-p"></i> </a>
                                    </li>
                                    <li>
                                        <a href="#"> <i class="fa fa-dribbble"></i> </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            @endforeach 

        </div>
    </div>
</section>


<section class="py-50" data-aos="fade-up">
    <div class="container">
        <div class="row">
            <div class="col-xl-4 col-12">
                <div class="box">
                    <div class="box-body d-flex align-items-center">
                        <div class="d-flex flex-column flex-grow-1">
                            <a href="#" class="box-title text-muted fw-600 fs-18 mb-2 hover-primary">{{ trans('home.schoolfounder') }}</a>
                            <span class="fw-500 text-fade">{{ trans('home.arouci') }}</span>
                        </div>
                        <img src="{{asset('images/arouci.jpg')}}"  alt="" class="rounded-circle max-w-80">
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4 col-12">
                <a href="#" class="box bg-info bg-hover-info">
                    <div class="box-body">
                        <div class="d-flex align-items-center">
                            <div class="w-80 h-80 l-h-100 rounded-circle b-1 border-white text-center">
                                <span class="text-white icon-Mail fs-40"></span>
                            </div>
                            <div class="ms-10">
                                <h3 class="text-white mb-0">contact@sobolnajah.com</h3>
                                <h4 class="text-white-50">contact-dbila@sobolnajah.com</h4>
                            </div>
                        </div>							
                    </div>
                </a>
            </div>

            <div class="col-xl-4 col-12">
                <a href="#" class="box bg-warning bg-hover-warning">
                    <div class="box-body">
                        <div class="d-flex align-items-center">
                            <div class="w-80 h-80 l-h-100 rounded-circle b-1 border-white text-center">
                                <span class="text-white icon-Phone fs-40"><span class="path1"></span><span class="path2"></span></span>
                            </div>
                            <div class="ms-10">
                                <h3 class="text-white mb-0">{{ trans('contact.phone1') }}</h3>
                                <h4 class="text-white-50">{{ trans('contact.phone2') }}</h4>
                            </div>
                        </div>							
                    </div>
                </a>
            </div>
        </div>	
    </div>
</section>