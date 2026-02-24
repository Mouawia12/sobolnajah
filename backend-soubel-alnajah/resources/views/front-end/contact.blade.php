@extends('layouts.masterhome')
@section('css')

@section('title')
   {{ trans('main_header.contact') }}
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

<!--Page content -->
	
<section class="py-50">
    <div class="container">
        <div class="row align-items-center">
           


            <div class="col-md-6 col-12 mt-30 ">
                <div class="box box-body p-40 bg-dark mb-0">
                    <h2 class="box-title text-white">{{ trans('contact.contactinfo') }}</h2>
                    <p>{{ trans('contact.sobolnajah') }}</p>
                    <div class="widget fs-18 my-20 py-20 by-1 border-light">	
                        <ul class="list list-unstyled text-white-80">
                            <li class="ps-40"><i class="ti-location-pin"></i>{{ trans('contact.adresserimel') }} </li>
                            <li class="ps-40 my-20"><i class="ti-mobile"></i> <span>{{ trans('contact.phone1') }} </span><br><span>{{ trans('contact.phone2') }}</span></li>
                            <li class="ps-40 my-20"><i class="fa fa-phone"></i> {{ trans('contact.fax1') }} </li>

                            <li class="ps-40"><i class="ti-email"></i><span>contact@sobolnajah.com</span></li>

                        </ul>
                    </div>
                    <h4 class="mb-20">{{ trans('contact.follow') }}</h4>
                    <ul class="list-unstyled d-flex gap-items-1">
                        <li><a href="https://www.facebook.com/%D9%85%D8%AF%D8%B1%D8%B3%D8%A9-%D8%B3%D8%A8%D9%84-%D8%A7%D9%84%D9%86%D8%AC%D8%A7%D8%AD-%D8%A7%D9%84%D8%AE%D8%A7%D8%B5%D8%A9-%D8%A8%D8%A7%D9%84%D9%88%D8%A7%D8%AF%D9%8A-1732169393669163" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="https://www.instagram.com/ecolenadjah39/" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram"><i class="fa fa-instagram"></i></a></li>
                        {{-- <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-linkedin"><i class="fa fa-linkedin"></i></a></li> --}}
                        {{-- <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-youtube"><i class="fa fa-youtube"></i></a></li> --}}
                    </ul>
                </div>
            </div>



            <div class="col-md-6 col-12 mt-30 ">
              <div class="box box-body p-40 bg-dark mb-0">
                  <h2 class="box-title text-white">{{ trans('contact.contactinfo') }}</h2>
                  <p>{{ trans('contact.sobolnajahdbila') }}</p>
                  <div class="widget fs-18 my-20 py-20 by-1 border-light">	
                      <ul class="list list-unstyled text-white-80">
                          <li class="ps-40"><i class="ti-location-pin"></i>{{ trans('contact.adressedbila') }}</li>
                          <li class="ps-40 my-20"><i class="ti-mobile"></i> <span>{{ trans('contact.phone3') }} </span><br><span>{{ trans('contact.phone4') }}</span></li>
                          <li class="ps-40 my-20"><i class="fa fa-phone"></i> {{ trans('contact.fax2') }} </li>

                          <li class="ps-40"><i class="ti-email"></i><span>contact-dbila@sobolnajah.com</span></li>

                      </ul>
                  </div>
                  <h4 class="mb-20">{{ trans('contact.follow') }}</h4>
                  <ul class="list-unstyled d-flex gap-items-1">
                      <li><a href="https://www.facebook.com/%D9%85%D8%AF%D8%B1%D8%B3%D8%A9-%D8%B3%D8%A8%D9%84-%D8%A7%D9%84%D9%86%D8%AC%D8%A7%D8%AD-%D8%A7%D9%84%D8%AE%D8%A7%D8%B5%D8%A9-%D8%A8%D8%A7%D9%84%D9%88%D8%A7%D8%AF%D9%8A-1732169393669163" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook"><i class="fa fa-facebook"></i></a></li>
                      <li><a href="https://www.instagram.com/ecolenadjah39/" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram"><i class="fa fa-instagram"></i></a></li>
                      {{-- <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-linkedin"><i class="fa fa-linkedin"></i></a></li> --}}
                      {{-- <li><a href="#" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-youtube"><i class="fa fa-youtube"></i></a></li> --}}
                  </ul>
              </div>
          </div>

          <div class="col-12">
            <form class="contact-form" action="#">
                <div class="text-start mb-30">
                    <h2>{{ trans('contact.Getintouch') }}</h2>
                    {{-- <p>It is a long established fact that a reader will be distracted by the readable content of a page</p> --}}
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="text" class="form-control" placeholder="{{ trans('contact.firstname') }}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="text" class="form-control" placeholder="{{ trans('contact.lastname') }}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="email" class="form-control" placeholder="{{ trans('contact.email') }}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="tel" class="form-control" placeholder="{{ trans('contact.phone') }}">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <input type="text" class="form-control" placeholder="{{ trans('contact.subject') }}">
                    </div>
                  </div>
                  {{-- <div class="col-md-6">
                      <div class="form-group">
                          <select class="form-select">
                            <option>Select Courses</option>
                            <option>General</option>
                            <option>IT & Software</option>
                            <option>Photography</option>
                            <option>Programming Language</option>
                            <option>Technology</option>
                          </select>
                        </div>
                  </div> --}}
                  <div class="col-lg-12">
                      <div class="form-group">
                        <textarea name="message" rows="5" class="form-control" placeholder="{{ trans('contact.message') }}"></textarea>
                      </div>
                  </div>
                  <div class="col-lg-12">
                      <button name="submit" type="submit" value="Submit" class="btn btn-dark">{{ trans('contact.sendmessage') }}</button>
                  </div>
                </div>
            </form>
        </div>

        </div>
    </div>
</section>


<section>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3332.8087473841315!2d6.8557466!3d33.34994599999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12591137aed7b9c5%3A0x3200aba34e8191f9!2z2YXYr9ix2LPYqSDYp9mE2YbYrNin2K0g2KfZhNiu2KfYtdip!5e0!3m2!1sen!2sdz!4v1653817521002!5m2!1sen!2sdz" class="map" style="border:0" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</section>
@endsection


@section('js')
    
@endsection