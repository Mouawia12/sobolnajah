<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

	@include('layoutsadmin.head')

  </head>

    @if (App::isLocale('ar'))
        <body class="hold-transition light-skin sidebar-mini theme-primary fixed rtl">
      @else
        <body class="hold-transition light-skin sidebar-mini theme-primary fixed">
    @endif
	
<div class="wrapper">
	<div id="loader"></div>
	
  @include('layoutsadmin.main_header')

  @include('layoutsadmin.main_sidebar')
  

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  <div class="container-full">
		<!-- Main content -->
		<section class="content">
				@include('layoutsadmin.partials.page_header')
				@include('layoutsadmin.partials.status_alerts')
				
				@yield('contenta')

		</section>
		<!-- /.content -->
	  </div>
  </div>


  @include('layoutsadmin.footer')

  @include('layoutsadmin.chatbar')

  @include('layoutsadmin.js')

</body>
</html>
