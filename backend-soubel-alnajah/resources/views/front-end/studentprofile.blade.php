@extends('layouts.masterhome')
@section('css')

@section('title')
   student profile
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

{{-- -------------------------------------------------------------------------- --}}

<section class="py-50">
    <div class="container">
        <div class="row">
            
            <div class="col-lg-3 col-md-4 col-12">
                <div class="box position-sticky t-100">
                    <div class="box-body text-center">
                        <div class="mb-20 mt-20">
                            @if ( $StudentInfo->gender == 1 )
                            <img src="{{ asset('/images/avatar/5.jpg')}}" width="150" class="rounded-circle bg-info-light" alt="user">
                            @else
                            <img src="{{ asset('/images/avatar/2.jpg')}}" width="150" class="rounded-circle bg-info-light" alt="user">
                            @endif
                            <h4 class="mt-20 mb-0">{{ $StudentInfo->prenom }}&nbsp;{{ $StudentInfo->nom }}</h4>
                            <a href="mailto:{{ $StudentInfo->user->email }}">{{ $StudentInfo->user->email }}</a>
                        </div>
                        <div class="badge badge-info-light fs-16">{{ $StudentInfo->section->classroom->schoolgrade->school->name_school }}</div>
                        {{-- <div class="badge badge-primary-light fs-16">UI</div>
                        <div class="badge badge-danger-light fs-16">UX</div>
                        <div class="badge badge-warning-light fs-16" data-bs-toggle="tooltip" data-placement="top" title="" data-original-title="3 more">+10</div> --}}
                        <ul class="list-inline text-center mt-20">
                            <li><a href="javascript:void(0)" data-bs-toggle="tooltip" title="" data-original-title="Facebook"><i class="fa fa-facebook-square fs-20"></i></a></li>
                            <li><a href="javascript:void(0)" data-bs-toggle="tooltip" title="" data-original-title="Twitter"><i class="fa fa-twitter-square fs-20"></i></a></li>
                            <li><a href="javascript:void(0)" data-bs-toggle="tooltip" title="" data-original-title="Instagram"><i class="fa fa-instagram fs-20"></i></a></li>
                            <li><a href="javascript:void(0)" data-bs-toggle="tooltip" title="" data-original-title="Linkedin"><i class="fa fa-linkedin-square fs-20"></i></a></li>
                        </ul>							
                        {{-- <ul class="cours-star">
                            <li class="active"><i class="fa fa-star"></i></li>
                            <li class="active"><i class="fa fa-star"></i></li>
                            <li class="active"><i class="fa fa-star"></i></li>
                            <li><i class="fa fa-star"></i></li>
                            <li><i class="fa fa-star"></i></li>
                        </ul> --}}
                    </div>
                    <div class="p-15 bt-1 bb-1">
                        <div class="row text-center">
                            <div class="col-6 be-1">
                                <a href="#" class="link d-flex align-items-center justify-content-center font-medium">
                                    <span class="icon-Mail fs-20 me-5"></span>{{ trans('student.message') }}</a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('Exames.index')}}" class="link d-flex align-items-center justify-content-center font-medium">
                                    <span class="icon-Code1 fs-20 me-5"><span class="path1"></span><span class="path2"></span></span>{{ trans('student.library') }}</a>
                            </div>
                        </div>						
                    </div>
                    <ul class="nav d-block nav-stacked" id="pills-tab23" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pills-personal-tab" data-bs-toggle="pill" href="#pills-personal" role="tab" aria-controls="pills-personal" aria-selected="true">
                                <i class="me-10 mdi mdi-account"></i>{{ trans('student.informationstudent') }}
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link" id="pills-courses-tab" data-bs-toggle="pill" href="#pills-courses" role="tab" aria-controls="pills-courses" aria-selected="true">
                                <i class="me-10 mdi mdi-book"></i> الدروس<span class="pull-right badge bg-info-light">1310</span>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="nav-link" data-bs-target="#modal-status" data-bs-toggle="modal"  >
                                <i class="me-10 mdi mdi-bookmark-plus"></i>{{ trans('student.schoolcertificaterequest') }}
                                {{-- <span class="pull-right badge bg-success-light">120</span> --}}
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="true">
                                <i class="me-10 mdi mdi-account"></i>Edit Profile
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="nav-link" id="pills-password-tab" data-bs-toggle="pill" href="#pills-password" role="tab" aria-controls="pills-password" aria-selected="true">
                                <i class="me-10 mdi mdi-lock"></i>{{ trans('student.changePassword') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- update  status-->
<div class="modal center-modal fade" id="modal-status" tabindex="-1">
    <div class="modal-dialog">
     <div class="modal-content">
       <div class="modal-body">
          <form id="status-form" action="{{ route('notify', Auth::user()->id)}}" method="GET" > 
             
            {{ method_field('GET') }}
            @csrf
             <div class="box-body">
                <div class="row">
                  <div class="form-group text-center">
                    <label class="form-label">{{ trans('student.schoolyear') }}</label><br>
                    <input type="text"  name="year"/>
                    <input type="hidden" name="namefr" value="{{ $StudentInfo->getTranslation('prenom', 'fr') }}">
                    <input type="hidden" name="namear" value="{{ $StudentInfo->getTranslation('prenom', 'ar') }}">
                  </div>
                </div>
             </div>
          </form>
       </div>
       <div class="modal-footer modal-footer-uniform">
        <a type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ trans('opt.close') }}</a>
        <a type="button" class="btn btn-success float-end" onclick="event.preventDefault();
        document.getElementById('status-form').submit();">{{ trans('opt.save') }}</a>
       </div>
     </div>
    </div>
    </div>
            <div class="col-lg-9 col-md-8 col-12">
                <div class="box">
                    <div class="box-body">
                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                              <h6>{{ $error }}</h6>
                                </div>
                            @endforeach
                        @endif
                        @if(Session::get('success')=='a')   
                            <div class="alert alert-success alert-dismissible">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <h6>لقد تم اسال طلب شهادة مدرسية, سيتم الاتصال بك قريبا شكرا</h6>
                            </div>
                        @endif
                        @if(Session::get('success')=='b')   
                            <div class="alert alert-success alert-dismissible">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <h6>تم تغيير كلمة السر بنجاح</h6>
                            </div>
                        @endif

                        <div class="tab-content" id="pills-tabContent23">
                            <div class="tab-pane fade show active" id="pills-personal" role="tabpanel" aria-labelledby="pills-personal-tab">
                                <h4 class="box-title mb-0">
                                    {{ trans('student.informationstudent') }}
                                </h4>
                                <hr>
                                <ul class="list-unstyled clearfix">
                                    <li class="w-md-p50 float-start pb-10">
                                        <a href="#" class="text-dark d-flex justify-content-between pe-50">
                                            <span class="fw-500">{{ trans('student.name') }}</span>
                                            <span class="text-muted">{{ $StudentInfo->prenom }}&nbsp;{{ $StudentInfo->nom }}</span>
                                        </a>
                                    </li>
                                    <li class="w-md-p50 float-start pb-10">
                                        <a href="#" class="text-dark d-flex justify-content-between">
                                            <span class="fw-500">{{ trans('student.adresse') }}</span>
                                            <span class="text-muted">{{ $StudentInfo->wilaya }}&nbsp;,{{ $StudentInfo->baladia }}</span>
                                        </a>
                                    </li>
                                    <li class="w-md-p50 float-start pb-10">
                                        <a href="#" class="text-dark d-flex justify-content-between pe-50">
                                            <span class="fw-500">{{ trans('inscription.section') }}</span>
                                            <span class="text-muted">&nbsp;{{ $StudentInfo->section->classroom->name_class }}&nbsp;{{ $StudentInfo->section->name_section}}</span>
                                        </a>
                                    </li>
                                    <li class="w-md-p50 float-start pb-10">
                                        <a href="#" class="text-dark d-flex justify-content-between">
                                            <span class="fw-500">{{ trans('inscription.datenaissance') }}</span>
                                            <span class="text-muted">{{ $StudentInfo->datenaissance}}</span>
                                        </a>
                                    </li>
                                    <li class="w-md-p50 float-start pb-10">
                                        <a href="#" class="text-dark d-flex justify-content-between pe-50">
                                            <span class="fw-500">{{ trans('inscription.email') }}</span>
                                            <span class="text-muted">{{ $StudentInfo->user->email }}</span>
                                        </a>
                                    </li>
                                    <li class="w-md-p50 float-start pb-10">
                                        <a href="#" class="text-dark d-flex justify-content-between">
                                            <span class="fw-500">{{ trans('inscription.numtelephone') }}</span>
                                            <span class="text-muted">0{{ $StudentInfo->numtelephone }}</span>
                                        </a>
                                    </li>
                                </ul>
                                <hr>
                                {{-- <h4 class="box-title mb-0">
                                    اعلان
                                </h4>
                                <hr>
                                <p>
                                    يرجى من جميع الطلبة الالتحاق بمصلحة النقل للتسجيلات 
                                </p> --}}
                                {{-- <hr> --}}



                                {{-- <div class="popup-gallery">
                                    <div class="d-flex gap-items-2 mb-10">
                                        <a href="../images/front-end-img/courses/1.jpg" title="Caption. Can be aligned to any side and contain any HTML.">
                                            <img src="../images/front-end-img/courses/1.jpg" alt="" />
                                        </a>
                                        <a href="../images/front-end-img/courses/2.jpg" title="This image fits only horizontally.">
                                            <img src="../images/front-end-img/courses/2.jpg" alt="" />
                                        </a>
                                        <a href="../images/front-end-img/courses/3.jpg" title="Caption. Can be aligned to any side and contain any HTML.">
                                            <img src="../images/front-end-img/courses/3.jpg" alt="" />
                                        </a>										
                                    </div>
                                    <div class="d-flex gap-items-2">
                                        <a href="../images/front-end-img/courses/4.jpg" title="Caption. Can be aligned to any side and contain any HTML.">
                                            <img src="../images/front-end-img/courses/4.jpg" alt="" />
                                        </a>
                                        <a href="../images/front-end-img/courses/5.jpg" title="Caption. Can be aligned to any side and contain any HTML.">
                                            <img src="../images/front-end-img/courses/5.jpg" alt="" />
                                        </a>
                                        <a href="../images/front-end-img/courses/6.jpg" title="Caption. Can be aligned to any side and contain any HTML.">
                                            <img src="../images/front-end-img/courses/6.jpg" alt="" />
                                        </a>										
                                    </div>
                                </div> --}}
                            </div>
                            <div class="tab-pane fade" id="pills-courses" role="tabpanel" aria-labelledby="pills-courses-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="box-title mb-0">
                                            الدروس
                                        </h4>
                                        <hr>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="card">
                                          <img class="card-img-top" src="../images/front-end-img/courses/1.jpg" alt="Card image cap">
                                          <div class="card-body">
                                            <h4 class="card-title justify-content-between d-flex align-items-center">Manegement
                                               <span class="badge badge-success">Online</span>
                                            </h4>
                                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                                          </div>
                                          <div class="card-footer justify-content-between d-flex align-items-center">
                                            <div class="d-flex fs-18 fw-600"> <span class="text-dark me-10">$83</span> <del class="text-muted">$195</del> </div>
                                            <span>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star-half text-warning"></i>
                                                <span class="text-muted ms-2">(12)</span>
                                            </span>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="card">
                                          <img class="card-img-top" src="../images/front-end-img/courses/9.jpg" alt="Card image cap">
                                          <div class="card-body">
                                            <h4 class="card-title justify-content-between d-flex align-items-center">Networking
                                               <span class="badge badge-success">Online</span>
                                            </h4>
                                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                                          </div>
                                          <div class="card-footer justify-content-between d-flex align-items-center">
                                            <div class="d-flex fs-18 fw-600"> <span class="text-dark me-10">$83</span> <del class="text-muted">$195</del> </div>
                                            <span>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star-half text-warning"></i>
                                                <span class="text-muted ms-2">(12)</span>
                                            </span>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="card">
                                          <img class="card-img-top" src="../images/front-end-img/courses/8.jpg" alt="Card image cap">
                                          <div class="card-body">
                                            <h4 class="card-title justify-content-between d-flex align-items-center">Security
                                               <span class="badge badge-success">Online</span>
                                            </h4>
                                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                                          </div>
                                          <div class="card-footer justify-content-between d-flex align-items-center">
                                            <div class="d-flex fs-18 fw-600"> <span class="text-dark me-10">$83</span> <del class="text-muted">$195</del> </div>
                                            <span>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star-half text-warning"></i>
                                                <span class="text-muted ms-2">(12)</span>
                                            </span>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="card">
                                          <img class="card-img-top" src="../images/front-end-img/courses/2.jpg" alt="Card image cap">
                                          <div class="card-body">
                                            <h4 class="card-title justify-content-between d-flex align-items-center">Language
                                               <span class="badge badge-success">Online</span>
                                            </h4>
                                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                                          </div>
                                          <div class="card-footer justify-content-between d-flex align-items-center">
                                            <div class="d-flex fs-18 fw-600"> <span class="text-dark me-10">$83</span> <del class="text-muted">$195</del> </div>
                                            <span>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star-half text-warning"></i>
                                                <span class="text-muted ms-2">(12)</span>
                                            </span>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="card">
                                          <img class="card-img-top" src="../images/front-end-img/courses/10.jpg" alt="Card image cap">
                                          <div class="card-body">
                                            <h4 class="card-title justify-content-between d-flex align-items-center">It &amp; software
                                               <span class="badge badge-success">Online</span>
                                            </h4>
                                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                                          </div>
                                          <div class="card-footer justify-content-between d-flex align-items-center">
                                            <div class="d-flex fs-18 fw-600"> <span class="text-dark me-10">$83</span> <del class="text-muted">$195</del> </div>
                                            <span>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star-half text-warning"></i>
                                                <span class="text-muted ms-2">(12)</span>
                                            </span>
                                          </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="card">
                                          <img class="card-img-top" src="../images/front-end-img/courses/5.jpg" alt="Card image cap">
                                          <div class="card-body">
                                            <h4 class="card-title justify-content-between d-flex align-items-center">Photography
                                               <span class="badge badge-success">Online</span>
                                            </h4>
                                            <p class="card-text">This is a wider card with supporting text below as a natural lead-in to additional content. This content is a little bit longer.</p>
                                          </div>
                                          <div class="card-footer justify-content-between d-flex align-items-center">
                                            <div class="d-flex fs-18 fw-600"> <span class="text-dark me-10">$83</span> <del class="text-muted">$195</del> </div>
                                            <span>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star text-warning"></i>
                                                <i class="fa fa-star-half text-warning"></i>
                                                <span class="text-muted ms-2">(12)</span>
                                            </span>
                                          </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="tab-pane fade" id="pills-followers" role="tabpanel" aria-labelledby="pills-followers-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <h4 class="box-title mb-0">
                                            Followers  
                                        </h4>
                                        <hr>
                                    </div>
                                    <div class="col-md-6 col-12">											
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-1.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-primary mb-1 fs-16">Sophia</a>
                                                <span class="text-mute">sophia@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-2.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-danger mb-1 fs-16">Mason</a>
                                                <span class="text-mute">mason@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-3.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-success mb-1 fs-16">Emily</a>
                                                <span class="text-mute">emily@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-4.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-info mb-1 fs-16">Daniel</a>
                                                <span class="text-mute">daniel@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-25">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-5.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-warning mb-1 fs-16">Natalie</a>
                                                <span class="text-mute">natalie@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-6.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-primary mb-1 fs-16">Clark</a>
                                                <span class="text-mute">clark@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-7.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-danger mb-1 fs-16">Rock</a>
                                                <span class="text-mute">rock@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-8.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-success mb-1 fs-16">Paton</a>
                                                <span class="text-mute">paton@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-9.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-info mb-1 fs-16">Don</a>
                                                <span class="text-mute">don@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-25">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-10.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-warning mb-1 fs-16">Amenda</a>
                                                <span class="text-mute">amenda@dummy.com</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">											
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-11.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-primary mb-1 fs-16">Sophia</a>
                                                <span class="text-mute">sophia@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-12.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-danger mb-1 fs-16">Mason</a>
                                                <span class="text-mute">mason@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-13.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-success mb-1 fs-16">Emily</a>
                                                <span class="text-mute">emily@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-10.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-info mb-1 fs-16">Daniel</a>
                                                <span class="text-mute">daniel@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-25">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-15.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-warning mb-1 fs-16">Natalie</a>
                                                <span class="text-mute">natalie@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-16.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-primary mb-1 fs-16">Clark</a>
                                                <span class="text-mute">clark@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-1.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-danger mb-1 fs-16">Rock</a>
                                                <span class="text-mute">rock@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-4.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-success mb-1 fs-16">Paton</a>
                                                <span class="text-mute">paton@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-30">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-5.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-info mb-1 fs-16">Don</a>
                                                <span class="text-mute">don@dummy.com</span>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-25">
                                            <div class="me-15">
                                                <img src="../images/avatar/avatar-8.png" class="avatar avatar-lg rounded10 bg-primary-light" alt="">
                                            </div>
                                            <div class="d-flex flex-column fw-500">
                                                <a href="#" class="text-dark hover-warning mb-1 fs-16">Amenda</a>
                                                <span class="text-mute">amenda@dummy.com</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                            {{-- <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">									
                                <div class="row">
                                    <div class="col-12">
                                        <form class="form">
                                            <div>
                                                <h4 class="box-title text-info"><i class="ti-user me-15"></i> Edit Profile</h4>
                                                <hr class="my-15">
                                                <div class="row">
                                                  <div class="col-md-6">

                                                    <div class="form-group">
                                                      <label class="form-label">First Name</label>
                                                      <input type="text" class="form-control" placeholder="First Name">
                                                    </div>
                                                  </div>
                                                  <div class="col-md-6">
                                                    <div class="form-group">
                                                      <label class="form-label">Last Name</label>
                                                      <input type="text" class="form-control" placeholder="Last Name">
                                                    </div>
                                                  </div>
                                                </div>
                                                <div class="row">
                                                  <div class="col-md-6">
                                                    <div class="form-group">
                                                      <label class="form-label">Company Name</label>
                                                      <input type="text" class="form-control" placeholder="Company Name">
                                                    </div>
                                                  </div>
                                                  <div class="col-md-6">
                                                    <div class="form-group">
                                                      <label class="form-label">Contact Number</label>
                                                      <input type="tel" class="form-control" placeholder="Phone">
                                                    </div>
                                                  </div>
                                                </div>
                                                <h4 class="box-title text-info mt-30"><i class="ti-envelope me-15"></i> Contact Info &amp; Bio</h4>
                                                <hr class="my-15">
                                                <div class="form-group">
                                                  <label class="form-label">Email</label>
                                                  <input class="form-control" type="email" placeholder="email">
                                                </div>
                                                <div class="form-group">
                                                  <label class="form-label">Website</label>
                                                  <input class="form-control" type="url" placeholder="http://">
                                                </div>
                                                <div class="form-group">
                                                  <label class="form-label">Contact Number</label>
                                                  <input class="form-control" type="tel" placeholder="Contact Number">
                                                </div>
                                                <div class="form-group">
                                                  <label class="form-label">Address</label>
                                                  <input class="form-control" type="text" placeholder="Address">
                                                </div>
                                                <div class="form-group">
                                                  <label class="form-label">Bio</label>
                                                  <textarea rows="4" class="form-control" placeholder="Bio"></textarea>
                                                </div>
                                                <h4 class="box-title text-info mt-30"><i class="ti-share me-15"></i> Social Profile</h4>
                                                <hr class="my-15">
                                                <div class="form-group">
                                                  <label class="form-label">Facebook</label>
                                                  <input class="form-control" type="text" placeholder="Facebook">
                                                </div>
                                                <div class="form-group">
                                                  <label class="form-label">Twitter</label>
                                                  <input class="form-control" type="text" placeholder="Twitter">
                                                </div>
                                                <div class="form-group">
                                                  <label class="form-label">Instagram</label>
                                                  <input class="form-control" type="text" placeholder="Instagram">
                                                </div>
                                                <div class="form-group">
                                                  <label class="form-label">Linkedin</label>
                                                  <input class="form-control" type="text" placeholder="Linkedin">
                                                </div>
                                                <hr class="my-15">
                                            </div>
                                            <div class="d-flex justify-content-end gap-items-2">
                                                <button type="submit" class="btn btn-success">
                                                  <i class="ti-save-alt"></i> Save changes
                                                </button>
                                                <button type="button" class="btn btn-danger">
                                                  <i class="ti-trash"></i> Cancel
                                                </button>
                                            </div>  
                                        </form>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="tab-pane fade" id="pills-password" role="tabpanel" aria-labelledby="pills-password-tab">
                                <div class="row">
                                    <div class="col-12">
                                        <form class="form" action="{{ route('changePassword')}}" method="POST">
                                            @csrf
                                            <div>
                                                <h4 class="box-title text-info"><i class="ti-user me-15"></i> {{ trans('student.changePassword') }}</h4>
                                                <hr class="mb-15">
                                                {{-- <div class="form-group">
                                                    <label class="form-label">User Name</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="ti-user"></i></span>
                                                        </div>
                                                        <input type="text" class="form-control" placeholder="Username">
                                                    </div>
                                                </div> --}}
                                             
                                                <div class="form-group">
                                                    <label class="form-label">{{ trans('student.password') }}</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="ti-lock"></i></span>
                                                        </div>
                                                        <input type="password" name="password" class="form-control" placeholder="{{ trans('student.password') }}">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">{{ trans('student.newpassword') }}</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="ti-lock"></i></span>
                                                        </div>
                                                        <input type="Password" name="newPassword" class="form-control" placeholder="{{ trans('student.newpassword') }}">
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">{{ trans('student.confirmnewpassword') }}</label>
                                                    <div class="input-group mb-3">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text"><i class="ti-lock"></i></span>
                                                        </div>
                                                        <input type="password" name="confirmNewPassword" class="form-control" placeholder="{{ trans('student.confirmnewpassword') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end gap-items-2">
                                                <button type="submit" class="btn btn-success">
                                                  <i class="ti-save-alt"></i>{{ trans('opt.save') }}
                                                </button>
                                            </div>  
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



@endsection


@section('js')
    
@endsection