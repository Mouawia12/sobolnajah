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

/* Unified rectangular media previews for publications/posts */
.blog-post .entry-image {
    overflow: hidden;
    border-radius: 14px;
    background: #edf1f6;
}

.blog-post .entry-image .owl-stage-outer,
.blog-post .entry-image .owl-stage,
.blog-post .entry-image .owl-item,
.blog-post .entry-image .item {
    height: clamp(220px, 32vw, 420px);
}

.blog-post .entry-image > img,
.blog-post .entry-image .item > img,
.blog-post .grid-post li img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

.blog-post .grid-post li {
    overflow: hidden;
    height: clamp(150px, 22vw, 260px);
}

@media (max-width: 767px) {
    .blog-post .entry-image .owl-stage-outer,
    .blog-post .entry-image .owl-stage,
    .blog-post .entry-image .owl-item,
    .blog-post .entry-image .item {
        height: 240px;
    }
}

</style>


