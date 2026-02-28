<header class="top-bar header-fancy-topbar clearfix bg-dark">
		<div class="topbar">

		  <div class="container">
			<div class="row justify-content-end">
			  <div class="col-lg-6 col-md-4 col-12">
				<div class="topbar-social text-center text-md-start topbar-left">
				  <ul class="list-inline d-md-flex d-inline-block">
					<li class="ms-10 pe-10"><a href="https://www.facebook.com/%D9%85%D8%AF%D8%B1%D8%B3%D8%A9-%D8%B3%D8%A8%D9%84-%D8%A7%D9%84%D9%86%D8%AC%D8%A7%D8%AD-%D8%A7%D9%84%D8%AE%D8%A7%D8%B5%D8%A9-%D8%A8%D8%A7%D9%84%D9%88%D8%A7%D8%AF%D9%8A-1732169393669163"><span class="text-white ti-facebook"></span></a></li>
					<li class="ms-10 pe-10"><a href="https://www.instagram.com/ecolenadjah39/"><span class="text-white ti-instagram"></span></a></li>
					<li class="ms-10 pe-10"><a href="https://www.youtube.com/channel/UCL7Lo3O794hhVipQA8nen2A"><span class="text-white ti-youtube"></span></a></li>
				  </ul>
				</div>
			  </div>

			  <div class="col-lg-6 col-md-8 col-12 xs-mb-10">
				
				<div class="topbar-call text-center text-md-end topbar-right">
					
				  <ul class="list-inline d-md-flex justify-content-end">
					
					 <li class="me-10 ps-10 lng-drop">
						<select class="header-lang-bx selectpicker" onchange="window.location = this.options[this.selectedIndex].value">
							<option data-icon="wi wi-direction-down">{{ trans('main_header.langue') }}</option>   
							@foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
							@if ($properties['native'] == "français")
							<option value="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" data-icon="flag-icon flag-icon-fr">{{ $properties['native'] }}</option>
							@endif
							@if ($properties['native'] == "العربية")
								<option value="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" data-icon="flag-icon flag-icon-dz">{{ $properties['native'] }}</option>
							@endif
							@if ($properties['native'] == "English")
   								<option value="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" data-icon="flag-icon flag-icon-gb">{{ $properties['native'] }}</option>
							@endif
						  @endforeach
					    </select>
					 </li>

					 @guest
					 @if (Route::has('login'))
					 <li class="me-10 ps-10"><a href="{{ LaravelLocalization::localizeUrl('/login') }}"><i class="text-white mdi mdi-login"></i>{{ trans('main_header.signin') }}</a></li>

					 @endif
					 @if (Route::has('register'))
					 <li class="me-10 ps-10"><a href="{{route('Inscriptions.index')}}"><i class="text-white fa fa-user"></i>{{ trans('main_header.register') }}</a></li>
					 @endif
					 @endguest
					 @auth
		 
					 <li class="me-10 ps-10"><a href="{{ LaravelLocalization::localizeUrl('/home') }}"><i class="text-white fa fa-user d-md-inline-block d-none"></i>{{ trans('main_header.profile') }}</a></li>

					 <li class="me-10 ps-10"><a  href="#"
						 onclick="event.preventDefault();
									   document.getElementById('logout-form').submit();"><i class="text-white fa fa-lock"></i>
						 {{ trans('main_sidebar.logout') }}
					  </a>
					  <form id="logout-form" action="logout/{{ App::currentLocale()}}" method="POST" class="d-none">
						  @csrf
					  </form></li>
					 @endauth
				  </ul>
				</div>
			  </div>

			 </div>
		  </div>
		</div>

		<nav hidden class="nav-white header-fancy">
			<div class="nav-header">
				<a href="index.html" class="brand d-lg-none d-block">
                    <img style="width: 30%;
                    height: 30%; display: block;
                    margin-left: auto;
                    margin-right: auto;" src="{{ asset('images/logo.png')}}" alt=""/>
				</a>
				<button class="toggle-bar">
					<span class="ti-menu"></span>
				</button>
			</div>
			<ul class="menu">
				<li>
					<a href="{{ LaravelLocalization::localizeUrl('/') }}">{{ trans('main_header.accueil') }}</a>
				</li>
				<li >
					<a href="{{route('Inscriptions.index')}}">{{ trans('main_header.inscription') }}</a>
				</li>
				
				
				<li >
					<a href="{{route('Publications.index')}}">{{ trans('main_header.agendascolaire') }}</a>
				</li>

				<li >
					<a href="{{ route('public.gallery.index') }}">{{ trans('main_header.gallery') }}</a>
				</li>
				<li>
					<a href="{{ route('public.jobs.index') }}">{{ trans('main_header.recruitment') }}</a>
				</li>
				<li>
					<a href="{{ route('public.timetables.index') }}">{{ trans('main_header.timetables') }}</a>
				</li>
				<li>
					<a href="{{ route('public.teacher_schedules.index') }}">{{ trans('main_sidebar.teacher_schedules') }}</a>
				</li>



				{{-- <li class="megamenu">
					<a href="/exam">{{ trans('main_header.examens') }}</a>
					<div class="megamenu-content">
						<div class="row">
							<div class="col-lg-3 col-12">
								<ul class="list-group">
									<li><h4 class="menu-title text-primary">ابتدائي</h4></li>
									<li><a href="faqs.html"><i class="ti-arrow-circle-right me-10"></i>سنة اولى</a></li>
									<li><a href="inovice.html"><i class="ti-arrow-circle-right me-10"></i>سنة ثانية</a></li>
									<li><a href="membership.html"><i class="ti-arrow-circle-right me-10"></i>سنة ثالثة</a></li>
									<li><a href="mydashboard.html"><i class="ti-arrow-circle-right me-10"></i>سنة رابعة</a></li>
									<li><a href="staff.html"><i class="ti-arrow-circle-right me-10"></i>سنة خامسة</a></li>
								</ul>
							</div>
							<div class="col-lg-3 col-12">
								<ul class="list-group">
									<li><h4 class="menu-title text-primary">متوسط</h4></li>
									<li><a href="faqs.html"><i class="ti-arrow-circle-right me-10"></i>سنة اولى</a></li>
									<li><a href="inovice.html"><i class="ti-arrow-circle-right me-10"></i>سنة ثانية</a></li>
									<li><a href="membership.html"><i class="ti-arrow-circle-right me-10"></i>سنة ثالثة</a></li>
									<li><a href="mydashboard.html"><i class="ti-arrow-circle-right me-10"></i>سنة رابعة</a></li>
								</ul>
							</div>
							<div class="col-lg-3 col-12">
								<ul class="list-group">
									<li><h4 class="menu-title text-primary">ثانوي</h4></li>
									<li><a href="faqs.html"><i class="ti-arrow-circle-right me-10"></i>سنة اولى</a></li>
									<li><a href="inovice.html"><i class="ti-arrow-circle-right me-10"></i>سنة ثانية</a></li>
									<li><a href="membership.html"><i class="ti-arrow-circle-right me-10"></i>سنة ثالثة</a></li>
								</ul>
							</div>
							<div class="col-md-3 col-12 d-none d-lg-block">
								<img src="../images/front-end-img/adv.jpg" class="img-fluid" alt="" />
							</div>
						</div>
					</div>
				</li> --}}

				<li>
					<a href="{{route('Exames.index')}}">{{ trans('main_header.examens') }}</a>
				</li>



				<li>
					<a href="{{ LaravelLocalization::localizeUrl('/about') }}">{{ trans('main_header.apropos') }}</a>
				</li>
				<li>
					<a href="{{ LaravelLocalization::localizeUrl('/contact') }}">{{ trans('main_header.contact') }}</a>
				</li>
			</ul>
			<ul class="attributes">
				<li><a href="#" class="toggle-search-fullscreen"><span class="ti-search"></span></a></li>
				<li class="megamenu" data-width="270">
					{{-- <a href="#"><span class="ti-shopping-cart"></span></a>
					<div class="megamenu-content megamenu-cart">
						<!-- Start Shopping Cart -->
						<div class="cart-header">
							<div class="total-price">
								Total:  <span>$2,432.93</span>
							</div>
							<i class="ti-shopping-cart"></i>
							<span class="badge">3</span>
						</div>
						<div class="cart-body">
							<ul>
								<li>
									<img src="../images/front-end-img/product/product-1.png" alt="">
									<h5 class="title">Lorem ipsum dolor</h5>
									<span class="qty">Quantity: 02</span>
									<span class="price-st">$843,12</span>
									<a href="#" class="link"></a>
								</li>
								<li>
									<img src="../images/front-end-img/product/product-2.png" alt="">
									<h5 class="title">Lorem ipsum dolor</h5>
									<span class="qty">Quantity: 02</span>
									<span class="price-st">$843,12</span>
									<a href="#" class="link"></a>
								</li>
								<li>
									<img src="../images/front-end-img/product/product-3.png" alt="">
									<h5 class="title">Lorem ipsum dolor</h5>
									<span class="qty">Quantity: 02</span>
									<span class="price-st">$843,12</span>
									<a href="#" class="link"></a>
								</li>
							</ul>
						</div>
						<div class="cart-footer">
							<a href="#">Checkout</a>
						</div> --}}
						<!-- End Shopping Cart -->
					</div>
				</li>
			</ul>

			<div class="wrap-search-fullscreen">
				<div class="container">
					<button class="close-search"><span class="ti-close"></span></button>
					<input type="text" placeholder="{{ trans('home.wdyw') }}" />
				</div>
			</div>
		</nav>
	</header>







