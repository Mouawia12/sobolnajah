@extends('layouts.masterhome')
@section('title', $schedule->title ?: trans('teacher_schedule.title'))

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container"><div class="text-center"><h2 class="page-title text-white">{{ $schedule->title ?: trans('teacher_schedule.title') }}</h2></div></div>
</section>

<section class="py-50">
    <div class="container">
        <div class="box">
            <div class="box-body">
                <div class="mb-2">
                    <a target="_blank" class="btn btn-secondary btn-sm" href="{{ route('public.teacher_schedules.print', $schedule) }}">{{ trans('teacher_schedule.print') }}</a>
                    <a class="btn btn-warning btn-sm" href="{{ route('public.teacher_schedules.pdf', $schedule) }}">{{ trans('teacher_schedule.pdf') }}</a>
                </div>
                <p><strong>{{ trans('teacher_schedule.teacher') }}:</strong> {{ $schedule->teacher->name ?? '—' }}</p>
                <p><strong>{{ trans('teacher_schedule.academic_year') }}:</strong> {{ $schedule->academic_year }}</p>
                @include('components.teacher_schedules.grid_display', ['slots' => $schedule->slots, 'days' => $days, 'matrix' => $matrix])
            </div>
        </div>
    </div>
</section>
@endsection
