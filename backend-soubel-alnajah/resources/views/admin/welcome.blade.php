@extends('layoutsadmin.masteradmin')

@section('titlea')
    {{ trans('roles.welcome_title') }}
@stop

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-body text-center py-50">
                <i class="mdi mdi-account-circle fs-60 text-primary"></i>
                <h3 class="mt-15">{{ trans('roles.welcome_title') }}، {{ auth()->user()->name }}</h3>
                <p class="text-muted mb-0">{{ trans('roles.welcome_no_sections') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
