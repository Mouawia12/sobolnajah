<footer class="footer_three">
    <div class="footer-top bg-dark3 pt-50">
        <div class="container">
            {{-- <div class="row">
                <div class="col-lg-3 col-12">
                    <div class="widget">
                        <h4 class="footer-title">About</h4>
                        <hr class="bg-info mb-10 mt-0 d-inline-block mx-auto w-60">
                        <p class="text-capitalize mb-20 ">
                            {{ trans('about.about') }}
                         </p>
                    </div>
                </div>											
                <div class="col-lg-3 col-12">
                    <div class="widget">
                        <h4 class="footer-title">{{ trans('contact.contactinfo') }}</h4>
                        <hr class="bg-info mb-10 mt-0 d-inline-block mx-auto w-60">
                        <ul class="list list-unstyled mb-30">
                            <li> <i class="fa fa-map-marker"></i> 123, Lorem Ipsum, Dummy City,<br>FL-12345 USA</li>
                            <li> <i class="ti-mobile"></i> <span>+(213) 77-715-5030 </span><br><span>+(213) 66-609-9101</span></li>
                            <li> <i class="fa fa-phone"></i> <span>032-14-25-93 </span></li>

                            <li> <i class="fa fa-envelope"></i><span>contact@sobolnajah.com</span><br><span>contact-dbila@sobolnajah.com</span></li>
                        </ul>
                    </div>
                </div>					
                <div class="col-12 col-lg-3">
                    <div class="widget widget_gallery clearfix">
                        <h4 class="footer-title"><a href="{{ route('public.gallery.index') }}">{{ trans('main_header.gallery') }}</a> </h4>
                        <hr class="bg-info mb-10 mt-0 d-inline-block mx-auto w-60">
                        <ul class="list-unstyled">
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                            <li><img src="{{ asset('agenda/1653329553fek8wbxC7.jpg')}}" alt=""></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-12">
                    <div class="widget">
                        <h4 class="footer-title">Accept Card Payments</h4>
                        <hr class="bg-info mb-10 mt-0 d-inline-block mx-auto w-60">
                        <ul class="payment-icon list-unstyled d-flex gap-items-1">
                            <li class="ps-0">
                                <a href="javascript:;"><i class="fa fa-cc-amex" aria-hidden="true"></i></a>
                            </li>
                            <li>
                                <a href="javascript:;"><i class="fa fa-cc-visa" aria-hidden="true"></i></a>
                            </li>
                            <li>
                                <a href="javascript:;"><i class="fa fa-credit-card-alt" aria-hidden="true"></i></a>
                            </li>
                            <li>
                                <a href="javascript:;"><i class="fa fa-cc-mastercard" aria-hidden="true"></i></a>
                            </li>
                            <li>
                                <a href="javascript:;"><i class="fa fa-cc-paypal" aria-hidden="true"></i></a>
                            </li>
                        </ul>
                        <h4 class="footer-title mt-20">Newsletter</h4>
                        <hr class="bg-info mb-4 mt-0 d-inline-block mx-auto w-60">
                        <div class="mb-20">
                            <form class="" action="" method="post">
                                <div class="input-group">
                                    <input name="email" required="required" class="form-control" placeholder="{{ trans('contact.youremail') }}" type="email">
                                    <button name="submit" value="Submit" type="submit" class="btn btn-info"> <i class="fa fa-envelope"></i> </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>				 --}}
        </div>
    </div>
    <div class="by-1 bg-dark3 py-10 border-dark">
        <div class="container">
            <div class="text-center footer-links">
                <a href="{{ LaravelLocalization::localizeUrl('/') }}" class="btn btn-link">{{ trans('main_header.accueil') }}</a>
                {{-- <a href="#" class="btn btn-link">About Us</a>
                <a href="#" class="btn btn-link">Courses</a> --}}
                <a href="{{route('Inscriptions.index')}}" class="btn btn-link">{{ trans('main_header.inscription') }}</a>
                <a href="{{route('Publications.index')}}" class="btn btn-link">{{ trans('main_header.agendascolaire') }}</a>
                <a href="{{route('public.jobs.index')}}" class="btn btn-link">{{ trans('main_header.recruitment') }}</a>
                <a href="{{route('public.timetables.index')}}" class="btn btn-link">{{ trans('main_header.timetables') }}</a>
                <a href="{{ LaravelLocalization::localizeUrl('/contact') }}" class="btn btn-link">{{ trans('main_header.contact') }}</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom bg-dark3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 col-12 text-md-start text-center">	
                    	2022 <a href="https://www.souftech.com/" style="color: rgb(0, 201, 251);"> SoufTech </a> {{ trans('footer.AllRightsReserved') }}
                </div>
                <div class="col-md-6 mt-md-0 mt-20">
                    <div class="social-icons">
                        <ul class="list-unstyled d-flex gap-items-1 justify-content-md-end justify-content-center">
                            <li><a href="https://www.facebook.com/%D9%85%D8%AF%D8%B1%D8%B3%D8%A9-%D8%B3%D8%A8%D9%84-%D8%A7%D9%84%D9%86%D8%AC%D8%A7%D8%AD-%D8%A7%D9%84%D8%AE%D8%A7%D8%B5%D8%A9-%D8%A8%D8%A7%D9%84%D9%88%D8%A7%D8%AF%D9%8A-1732169393669163" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-facebook"><i class="fa fa-facebook"></i></a></li>
                            <li><a href="https://www.instagram.com/ecolenadjah39/" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-instagram"><i class="fa fa-instagram"></i></a></li> 
                            <li><a href="https://www.youtube.com/channel/UCL7Lo3O794hhVipQA8nen2A" class="waves-effect waves-circle btn btn-social-icon btn-circle btn-youtube"><i class="fa fa-youtube"></i></a></li>  
                       </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
