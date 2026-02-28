@extends('layoutsadmin.masteradmin')
@section('titlea', trans('teacher_schedule.edit'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border"><h4 class="box-title">{{ trans('teacher_schedule.edit') }}</h4></div>
            <div class="box-body">
                <form method="POST" action="{{ route('teacher-schedules.update', $schedule) }}">
                    @csrf
                    @method('PATCH')
                    @include('admin.teacher_schedules._form', ['schedule' => $schedule, 'days' => $days, 'matrix' => $matrix, 'defaultSlots' => []])
                    <div class="mt-3">
                        <button class="btn btn-primary">{{ trans('teacher_schedule.update') }}</button>
                        <a href="{{ route('teacher-schedules.index') }}" class="btn btn-outline-secondary">{{ trans('timetable.back') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
