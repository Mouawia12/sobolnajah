@extends('layouts.masterhome')
@section('title', trans('teacher_schedule.title'))

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container"><div class="text-center"><h2 class="page-title text-white">{{ trans('teacher_schedule.title') }}</h2></div></div>
</section>

<section class="py-50">
    <div class="container">
        <form method="GET" class="row mb-4">
            <div class="col-md-4">
                <select name="teacher_id" class="form-select">
                    <option value="">{{ trans('teacher_schedule.teacher') }}</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected((string)request('teacher_id') === (string)$teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select name="academic_year" class="form-select">
                    <option value="">{{ trans('teacher_schedule.academic_year') }}</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected((string)request('academic_year') === (string)$year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary">{{ trans('teacher_schedule.filter') }}</button>
            </div>
        </form>

        <div class="row">
            @forelse($schedules as $schedule)
                <div class="col-md-6 mb-3">
                    <div class="box">
                        <div class="box-body">
                            <h4>{{ $schedule->title ?: trans('teacher_schedule.title') }}</h4>
                            <p>{{ trans('teacher_schedule.teacher') }}: {{ $schedule->teacher->name ?? '—' }}</p>
                            <p>{{ trans('teacher_schedule.academic_year') }}: {{ $schedule->academic_year }}</p>
                            <a class="btn btn-info" href="{{ route('public.teacher_schedules.show', $schedule) }}">{{ trans('teacher_schedule.show') }}</a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">{{ trans('teacher_schedule.empty') }}</div></div>
            @endforelse
        </div>

        {{ $schedules->links() }}
    </div>
</section>
@endsection
