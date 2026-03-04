@extends('layoutsadmin.masteradmin')
@section('titlea', trans('main_sidebar.teacher_weekly_schedule'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border"><h4 class="box-title">{{ trans('main_sidebar.teacher_weekly_schedule') }}</h4></div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <select name="academic_year" class="form-select">
                            <option value="">{{ trans('teacher_schedule.academic_year') }}</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" @selected((string)request('academic_year') === (string)$year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <button class="btn btn-primary">{{ trans('teacher_schedule.filter') }}</button>
                        <a class="btn btn-outline-secondary" href="{{ route('teacher.schedules.index') }}">{{ trans('teacher_schedule.reset') }}</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('teacher_schedule.academic_year') }}</th>
                            <th>{{ trans('teacher_schedule.status') }}</th>
                            <th>{{ trans('teacher_schedule.actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($schedules as $i => $schedule)
                            <tr>
                                <td>{{ $schedules->firstItem() + $i }}</td>
                                <td>{{ $schedule->academic_year }}</td>
                                <td>{{ $schedule->status === 'published' ? trans('teacher_schedule.published') : trans('teacher_schedule.draft') }}</td>
                                <td>
                                    <a class="btn btn-sm btn-info" href="{{ route('teacher.schedules.show', $schedule) }}">{{ trans('teacher_schedule.show') }}</a>
                                    <a class="btn btn-sm btn-secondary" target="_blank" href="{{ route('teacher.schedules.print', $schedule) }}">{{ trans('teacher_schedule.print') }}</a>
                                    <a class="btn btn-sm btn-warning" href="{{ route('teacher.schedules.pdf', $schedule) }}">{{ trans('teacher_schedule.pdf') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">{{ trans('teacher_schedule.empty') }}</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $schedules->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
