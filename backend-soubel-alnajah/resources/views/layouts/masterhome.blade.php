<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.head')
</head>
@if (App::isLocale('ar'))
<body class="theme-primary rtl">
@else
<body class="theme-primary">
@endif
 
    @include('layouts.main_header')
    
    @yield('content')
 
    
    
    @include('layouts.footer')
    
    @include('layouts.js')
</body>
</html>
