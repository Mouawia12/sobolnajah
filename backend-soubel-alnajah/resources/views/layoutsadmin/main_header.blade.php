<style>
	.notifications-menu .notify-trigger {
		width: 42px;
		height: 42px;
		border-radius: 50%;
		display: inline-flex !important;
		align-items: center;
		justify-content: center;
		position: relative;
		background: linear-gradient(135deg, #f8fbff, #edf3ff);
		border: 1px solid rgba(59, 130, 246, 0.25);
		box-shadow: 0 6px 14px rgba(15, 23, 42, 0.08);
		transform: translateY(-3px);
	}

	.notifications-menu .notify-trigger:hover {
		background: linear-gradient(135deg, #eef5ff, #e2edff);
		box-shadow: 0 10px 20px rgba(37, 99, 235, 0.18);
	}

	.notifications-menu .notify-bell {
		font-size: 20px;
		color: #1d4ed8;
		line-height: 1;
	}

	.notifications-menu .notify-count {
		position: absolute;
		top: -5px;
		right: -5px;
		min-width: 20px;
		height: 20px;
		padding: 0 6px;
		border-radius: 999px;
		background: #ef4444;
		color: #fff;
		font-size: 11px;
		font-weight: 700;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border: 2px solid #fff;
		box-shadow: 0 6px 12px rgba(239, 68, 68, 0.35);
	}

	body.dark-skin .notifications-menu .notify-trigger {
		background: linear-gradient(135deg, #16324e, #0f2740);
		border-color: rgba(96, 165, 250, 0.35);
	}

	body.dark-skin .notifications-menu .notify-bell {
		color: #93c5fd;
	}
</style>

<header class="main-header">
	<div class="d-flex align-items-center logo-box justify-content-start">
		<a href="#" class="waves-effect waves-light nav-link d-none d-md-inline-block mx-10 push-btn bg-transparent" data-toggle="push-menu" role="button">
			<span class="icon-Align-left"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span>
		</a>	
		<!-- Logo -->
		<a href="/home" class="logo">
		  <!-- logo-->
		  <div class="logo-lg text-center">
			  <span class="light-logo"><img style="width: 35%;height: 35%;background-color: white;border-radius: 50%;" src="{{ asset('images/logo.png')}}" alt="logo"/>
			  </span>
			  <span class="dark-logo"><img style="width: 35%;height: 35%;background-color: white;border-radius: 50%;" src="{{ asset('images/logo.png')}}" alt="logo"/>
			  </span>
			  {{-- <span class="light-logo"><img  src="{{ asset('images/huitnet-logo.png')}}" alt="logo"/>
			  </span>
			  <span class="dark-logo"><img   src="{{ asset('images/huitnet-logo.png')}}" alt="logo"/>
			  </span> --}}
		  </div>
		</a>	
	</div>  
    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
	  <div class="app-menu">
		<ul class="header-megamenu nav">
			<li class="btn-group nav-item d-md-none">
				<a href="#" class="waves-effect waves-light nav-link push-btn" data-toggle="push-menu" role="button">
					<span class="icon-Align-left"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span>
		    </a>
			</li>
			<li class="btn-group nav-item d-none d-xl-inline-block">
				<a href="{{ route('Chats.index') }}" class="waves-effect waves-light nav-link svg-bt-icon" title="{{ trans('opt.chat_users') }}">
					<i class="icon-Chat"><span class="path1"></span><span class="path2"></span></i>
		    </a>
			</li>
			<li class="btn-group nav-item d-none d-xl-inline-block">
				<a href="#" class="waves-effect waves-light nav-link svg-bt-icon" title="Toggle Theme" id="theme-toggle">
					<i class="mdi mdi-weather-night" id="theme-toggle-icon"></i>
		    </a>
			</li>
			<li class="btn-group nav-item d-none d-xl-inline-block">
				<a href="#" class="waves-effect waves-light nav-link svg-bt-icon" title="Mailbox">
					<i class="icon-Mailbox"><span class="path1"></span><span class="path2"></span></i>
		    </a>
			</li>
			<li class="btn-group nav-item d-none d-xl-inline-block">
				<a href="#" class="waves-effect waves-light nav-link svg-bt-icon" title="Taskboard">
					<i class="icon-Clipboard-check"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
			    </a>
			</li>
		</ul> 
	  </div>
		
      <div class="navbar-custom-menu r-side">
        <ul class="nav navbar-nav">	
			<li class="btn-group nav-item d-lg-inline-flex d-none">
				<a
					href="{{ route('site.home') }}"
					class="waves-effect waves-light nav-link svg-bt-icon"
					title="الصفحة الرئيسية"
					style="font-size: 24px; padding: 10px 14px;"
				>
					<i class="fa fa-home" style="font-size: 24px;"></i>
			    </a>
			</li>
			<li class="btn-group nav-item d-lg-inline-flex d-none">
				<a href="#" data-provide="fullscreen" class="waves-effect waves-light nav-link full-screen" title="Full Screen">
					<i class="icon-Expand-arrows"><span class="path1"></span><span class="path2"></span></i>
			    </a>
			</li>	  
			<li class="btn-group d-lg-inline-flex d-none">
				<div class="app-menu">
					<div class="search-bx mx-5">
						<form action="/" method="GET">
							<div class="input-group">
							  <input type="search" class="form-control" placeholder="{{ trans('opt.search') }}" aria-label="{{ trans('opt.search') }}" aria-describedby="button-addon2">
							  <div class="input-group-append">
								<button class="btn" type="button" id="button-addon3"><i class="ti-search"></i></button>
							  </div>
							</div>
						</form>
					</div>
				</div>
			</li>
				<?php
				   $i = 0;
				?>
				@foreach ($notify as $nt)
				@if($nt->read_at == null)
					<?php $i++; ?>
				@endif
			@endforeach
			  <!-- Notifications -->
			  <li class="dropdown notifications-menu">
				<a href="#" class="waves-effect waves-light dropdown-toggle notify-trigger" data-bs-toggle="dropdown" title="Notifications">
				  <i class="mdi mdi-bell-outline notify-bell"></i>
				  @if($i > 0)
				  	<span class="notify-count">{{ $i }}</span>
				  @endif
				</a>
			
			<ul class="dropdown-menu animated bounceIn">

			  <li class="header">
				<div class="p-20">
					<div class="flexbox">
						{{-- <div>
							<h4 class="mb-0 mt-0">Notifications</h4>
						</div> --}}
					</div>
				</div>
			  </li>

			  <li>
				<!-- inner menu: contains the actual data -->
				<ul class="menu sm-scrol">

					@foreach ($notify as $nt)
						@if($nt->read_at == null)
					<li>
						<form action="{{ route('markAsRead', $nt->id) }}" method="POST">
							@csrf
							<button type="submit" class="w-full text-start border-0 bg-transparent p-0">
								<i class="fa fa-users text-info"></i>
								@php
									$data = json_decode($nt->data, true) ?: [];
									$purposeLabels = [
										'enrollment' => trans('student.certificate_purpose_enrollment'),
										'scholarship' => trans('student.certificate_purpose_scholarship'),
										'administrative' => trans('student.certificate_purpose_administrative'),
										'other' => trans('student.certificate_purpose_other'),
									];
									$displayName = App::isLocale('fr')
										? ($data['namefr'] ?? ($data['namear'] ?? trans('student.name')))
										: ($data['namear'] ?? ($data['namefr'] ?? trans('student.name')));
									$purpose = $purposeLabels[$data['purpose'] ?? ''] ?? trans('student.certificate_purpose_other');
									$year = $data['year'] ?? '—';
									$copies = (int) ($data['copies'] ?? 1);
								@endphp
								<span class="fw-semibold">{{ $displayName }}</span>
								<span class="d-block text-muted small">{{ trans('student.certificate_modal_title') }} - {{ $purpose }}</span>
								<span class="d-block text-muted small">{{ trans('student.certificate_year') }}: {{ $year }} | {{ trans('student.certificate_copies') }}: {{ $copies }}</span>
							</button>
						</form>
					</li>
						@else
						<li>
							<form action="{{ route('markAsRead', $nt->id) }}" method="POST">
								@csrf
								<button type="submit" class="w-full text-start border-0 bg-transparent p-0">
									<i class="fa fa-users text-success"></i>
									@php
										$data = json_decode($nt->data, true) ?: [];
										$purposeLabels = [
											'enrollment' => trans('student.certificate_purpose_enrollment'),
											'scholarship' => trans('student.certificate_purpose_scholarship'),
											'administrative' => trans('student.certificate_purpose_administrative'),
											'other' => trans('student.certificate_purpose_other'),
										];
										$displayName = App::isLocale('fr')
											? ($data['namefr'] ?? ($data['namear'] ?? trans('student.name')))
											: ($data['namear'] ?? ($data['namefr'] ?? trans('student.name')));
										$purpose = $purposeLabels[$data['purpose'] ?? ''] ?? trans('student.certificate_purpose_other');
										$year = $data['year'] ?? '—';
										$copies = (int) ($data['copies'] ?? 1);
									@endphp
									<span class="fw-semibold">{{ $displayName }}</span>
									<span class="d-block text-muted small">{{ trans('student.certificate_modal_title') }} - {{ $purpose }}</span>
									<span class="d-block text-muted small">{{ trans('student.certificate_year') }}: {{ $year }} | {{ trans('student.certificate_copies') }}: {{ $copies }}</span>
								</button>
							</form>
						</li>
						@endif
					@endforeach


				  {{-- <li>
					<a href="#">
					  <i class="fa fa-users text-info"></i> Curabitur id eros quis nunc suscipit blandit.
					</a>
				  </li>
				  <li>
					<a href="#">
					  <i class="fa fa-warning text-warning"></i> Duis malesuada justo eu sapien elementum, in semper diam posuere.
					</a>
				  </li>
				  <li>
					<a href="#">
					  <i class="fa fa-users text-danger"></i> Donec at nisi sit amet tortor commodo porttitor pretium a erat.
					</a>
				  </li>
				  <li>
					<a href="#">
					  <i class="fa fa-shopping-cart text-success"></i> In gravida mauris et nisi
					</a>
				  </li>
				  <li>
					<a href="#">
					  <i class="fa fa-user text-danger"></i> Praesent eu lacus in libero dictum fermentum.
					</a>
				  </li>
				  <li>
					<a href="#">
					  <i class="fa fa-user text-primary"></i> Nunc fringilla lorem 
					</a>
				  </li>
				  <li>
					<a href="#">
					  <i class="fa fa-user text-success"></i> Nullam euismod dolor ut quam interdum, at scelerisque ipsum imperdiet.
					</a>
				  </li> --}}
				</ul>
			  </li>
			  <li class="footer">
				  <a href="#">View all</a>
			  </li>
			</ul>
		  </li>	
		  
	      <!-- User Account-->
          <li class="dropdown user user-menu">
            <a href="#" class="waves-effect waves-light dropdown-toggle" data-bs-toggle="dropdown" title="User">
				<i class="icon-User"><span class="path1"></span><span class="path2"></span></i>
            </a>
            <ul class="dropdown-menu animated flipInX">
              <li class="user-body">
				 {{-- <a class="dropdown-item" href="#"><i class="ti-user text-muted me-2"></i> Profile</a>
				 <a class="dropdown-item" href="#"><i class="ti-wallet text-muted me-2"></i> My Wallet</a> --}}
				 <a class="dropdown-item" href="{{ route('password.change.page') }}"><i class="ti-settings text-muted me-2"></i>{{ trans('main_header.setting') }}</a>
				 <div class="dropdown-divider"></div>
				 <a class="dropdown-item" href="#" onclick="event.preventDefault();
				 document.getElementById('logout-form').submit();">
				 <i class="ti-lock text-muted me-2"></i> {{ trans('main_sidebar.logout') }}</a>
                 <form id="logout-form" action="/logout/{{ App::currentLocale()}}" method="POST" >
                	@csrf
                 </form>
              </li>
            </ul>
          </li>	
		  
          <!-- Control Sidebar Toggle Button -->
          <li>
              <a href="#" data-toggle="control-sidebar" title="Setting" class="waves-effect waves-light">
			  	<i class="icon-Settings"><span class="path1"></span><span class="path2"></span></i>
			  </a>
          </li>
			
        </ul>
      </div>
    </nav>
  </header>
