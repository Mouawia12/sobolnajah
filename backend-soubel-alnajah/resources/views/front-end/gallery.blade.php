@extends('layouts.masterhome')
@section('css')
<style>
.grid-container {
  display: grid;
  /* الوضع الافتراضي للشاشات الكبيرة */
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 15px;
}

.location-listing {
  position: relative;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: transform 0.3s ease;
}
.location-listing:hover {
  transform: translateY(-5px);
}

.location-image img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  border-radius: 12px;
  transition: transform 0.4s ease;
}
.location-listing:hover img {
  transform: scale(1.1);
  filter: brightness(0.8);
}

/* Overlay title */
.location-title {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 10px;
  text-align: center;
  background: rgba(0,0,0,0.5);
  color: #fff;
  opacity: 0;
  transition: opacity 0.4s ease;
}
.location-listing:hover .location-title {
  opacity: 1;
}

/* ======== جديد: ثلاث أعمدة على الهواتف ======== */
@media (max-width: 767px) {
  .grid-container {
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }
  .location-image img {
    height: 120px; /* لتتناسب الصور مع الشاشة الصغيرة */
  }
}
</style>

<!-- Lightbox -->
<link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet"/>
@endsection





@section('title')
   {{ trans('main_header.gallery') }}
@stop

@section('content')
<!---page Title --->
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">						
                <h2 class="page-title text-white">{{ trans('main_header.gallery') }}</h2>
                <ol class="breadcrumb bg-transparent justify-content-center">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-white-50"><i class="mdi mdi-home-outline"></i></a></li>
                    <li class="breadcrumb-item text-white active">{{ trans('main_header.gallery') }}</li>
                </ol>
            </div>
        </div>
    </div>
</section>

<br><br>

<section class="content">
    <div class="grid-container">
      @foreach ($Gallery as $gal)
        @foreach(json_decode($gal->img_url, true) as $images)
          <article class="location-listing">
              <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('publications.media', now()->addHours(12), ['filename' => $images]) }}" class="glightbox" data-gallery="gallery1">
                <div class="location-image">
                    <img src="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('publications.media', now()->addHours(12), ['filename' => $images]) }}" alt="gallery">
                </div>
                <div class="location-title">{{ trans('main_header.gallery') }}</div>
              </a>
          </article>
        @endforeach 
      @endforeach 
    </div>
</section>

<br><br><br><br>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
  const lightbox = GLightbox({ selector: '.glightbox' });
</script>
@endsection
