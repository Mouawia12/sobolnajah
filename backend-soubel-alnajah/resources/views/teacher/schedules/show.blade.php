@extends('layoutsadmin.masteradmin')
@section('titlea', trans('teacher_schedule.weekly_schedule'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between">
                <h4 class="box-title">{{ $schedule->title ?: trans('teacher_schedule.weekly_schedule') }}</h4>
                <div>
                    <a class="btn btn-secondary btn-sm" target="_blank" href="{{ route('teacher.schedules.print', $schedule) }}">{{ trans('teacher_schedule.print') }}</a>
                    <a class="btn btn-warning btn-sm" href="{{ route('teacher.schedules.pdf', $schedule) }}">{{ trans('teacher_schedule.pdf') }}</a>
                </div>
            </div>
            <div class="box-body">
                @include('components.teacher_schedules.grid_display', ['slots' => $schedule->slots, 'days' => $days, 'matrix' => $matrix])
            </div>
        </div>
    </div>
</div>
@endsection
