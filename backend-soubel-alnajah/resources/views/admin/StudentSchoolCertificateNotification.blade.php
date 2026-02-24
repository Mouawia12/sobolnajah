@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   student notify
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">

        <div class="box">
        <div class="box-body text-center">
            <div class="mb-20 mt-20">
                <img src="{{ asset('images/avatar/avatar-12.png')}}" width="150" class="rounded-circle bg-info-light" alt="user" />
                <h4 class="mt-20 mb-0">{{ $StudentInfo->prenom }} {{ $StudentInfo->nom }}</h4>
                <a href="mailto:dummy@gmail.com">{{ $StudentInfo->user->email }}</a>
            </div>
            <div class="badge badge-pill badge-info-light fs-16">0{{ $StudentInfo->numtelephone }}</div>
            <div class="badge badge-pill badge-primary-light fs-16">{{ $StudentInfo->datenaissance}}</div>
            <div class="badge badge-pill badge-danger-light fs-16">{{ $StudentInfo->section->classroom->name_class}}</div>
            <div class="badge badge-pill badge-warning-light fs-16">{{ $StudentInfo->section->name_section}}</div>
            <div class="badge badge-pill badge-warning-light fs-16"> <?php $arryear = json_decode($arryear, true); print_r($arryear['year']); ?>   </div>

      </div>
        </div>

    </div>
</div>
@endsection


@section('jsa')

@endsection