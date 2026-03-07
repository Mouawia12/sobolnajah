@extends('layoutsadmin.masteradmin')

@section('titlea')
    {{ trans('opt.chat_users') }}
@stop

@section('cssa')
@include('chat.partials.styles')
@endsection

@section('contenta')
@include('chat.partials.body')
@endsection

@section('jsa')
@include('chat.partials.scripts')
@endsection
