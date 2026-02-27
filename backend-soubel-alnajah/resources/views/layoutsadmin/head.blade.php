<!-- Title -->
<title>@yield("titlea")</title>

<!-- Favicon -->
{{-- <link rel="shortcut icon" href="{{ URL::asset('assets/images/favicon.ico') }}" type="image/x-icon" /> --}}
<link rel="icon" href="{{ asset('images/logoicon.png') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">


<!-- Font -->
{{-- <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Poppins:200,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900">

<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/> --}}

@yield('cssa')
<!--- Style css -->
{{-- <link href="{{ URL::asset('assets/css/style.css') }}" rel="stylesheet"> --}}

<!--- Style css -->
{{-- @if (App::getLocale() == 'en')
    <link href="{{ URL::asset('assets/css/ltr.css') }}" rel="stylesheet">
@else
    <link href="{{ URL::asset('assets/css/rtl.css') }}" rel="stylesheet">
@endif --}}


<!-- Vendors Style-->
<link rel="stylesheet" href="{{ asset('cssadmin/vendors_css.css') }}?v={{ @filemtime(public_path('cssadmin/vendors_css.css')) }}">
	  
<!-- Style-->  
<link rel="stylesheet" href="{{ asset('cssadmin/style.css') }}?v={{ @filemtime(public_path('cssadmin/style.css')) }}">
<link rel="stylesheet" href="{{ asset('cssadmin/skin_color.css') }}?v={{ @filemtime(public_path('cssadmin/skin_color.css')) }}">
<link rel="stylesheet" href="{{ asset('cssadmin/admin-modern.css') }}?v={{ @filemtime(public_path('cssadmin/admin-modern.css')) }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Cairo">

@toastr_css


<style>
    body,div,p,h1,h2,h3,h4,h5,h6 {
      font-family: "Cairo", sans-serif;
    }
</style>
