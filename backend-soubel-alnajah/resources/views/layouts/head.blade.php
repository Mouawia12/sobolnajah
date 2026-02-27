<!-- Title -->
<title>@yield("title")</title>

<!-- Favicon -->
<link rel="icon" href="{{ asset('images/logoicon.png') }}">


<!-- Font -->
{{-- <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Poppins:200,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900">

<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/> --}}

@yield('css')
<!--- Style css -->
{{-- <link href="{{ URL::asset('assets/css/style.css') }}" rel="stylesheet"> --}}

<!--- Style css -->
{{-- @if (App::getLocale() == 'en')
    <link href="{{ URL::asset('assets/css/ltr.css') }}" rel="stylesheet">
@else
    <link href="{{ URL::asset('assets/css/rtl.css') }}" rel="stylesheet">
@endif --}}


<!-- Vendors Style-->
<link rel="stylesheet" href="{{ asset('css/vendors_css.css') }}">
	  
<!-- Style-->  
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/skin_color.css') }}">

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Cairo">

<style>
    body,div,p,h1,h2,h3,h4,h5,h6 {
      font-family: "Cairo", sans-serif;
    }
    .owl-dots {
    display: block !important;
    text-align: center;
    margin-top: 15px;
}
.owl-nav {
    display: block !important;
}
.owl-nav button {
    background: #333;
    color: #fff;
    border-radius: 50%;
    width: 30px;
    height: 30px;
}
/* Force-disable legacy cookie banner */
#gdpr-cookie-message {
    display: none !important;
}

</style>



