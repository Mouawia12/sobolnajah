@extends('layouts.masterhome')

@section('title')
    {{ trans('opt.chat_users') }}
@stop

@section('css')
@include('chat.partials.styles')
@endsection

@section('content')
<section class="py-30">
    <div class="container">
        @include('chat.partials.body')
    </div>
</section>
@endsection

@section('js')
@include('chat.partials.scripts')
@endsection
