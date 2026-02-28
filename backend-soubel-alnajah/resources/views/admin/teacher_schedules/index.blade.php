@extends('layoutsadmin.masteradmin')
@section('titlea', trans('teacher_schedule.title'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between">
                <h4 class="box-title">{{ trans('teacher_schedule.title') }}</h4>
                <a href="{{ route('teacher-schedules.create') }}" class="btn btn-info">{{ trans('teacher_schedule.add') }}</a>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-3">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ trans('teacher_schedule.search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="teacher_id" class="form-select">
                            <option value="">{{ trans('teacher_schedule.teacher') }}</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((string)request('teacher_id') === (string)$teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="academic_year" class="form-select">
                            <option value="">{{ trans('teacher_schedule.academic_year') }}</option>
                            @foreach($years as $year)
                                <option value="{{ $year }}" @selected((string)request('academic_year') === (string)$year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">{{ trans('teacher_schedule.status') }}</option>
                            <option value="draft" @selected(request('status')==='draft')>{{ trans('teacher_schedule.draft') }}</option>
                            <option value="published" @selected(request('status')==='published')>{{ trans('teacher_schedule.published') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary" type="submit">{{ trans('teacher_schedule.filter') }}</button>
                        <a class="btn btn-outline-secondary" href="{{ route('teacher-schedules.index') }}">{{ trans('teacher_schedule.reset') }}</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('teacher_schedule.teacher') }}</th>
                                <th>{{ trans('teacher_schedule.academic_year') }}</th>
                                <th>{{ trans('teacher_schedule.status') }}</th>
                                <th>{{ trans('teacher_schedule.visibility') }}</th>
                                <th>{{ trans('timetable.entries_count') }}</th>
                                <th>{{ trans('teacher_schedule.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $i => $schedule)
                                <tr>
                                    <td>{{ $schedules->firstItem() + $i }}</td>
                                    <td>
                                        <div>{{ $schedule->teacher->name ?? '—' }}</div>
                                        <small class="text-muted">{{ optional($schedule->teacher->user)->email }}</small>
                                    </td>
                                    <td>{{ $schedule->academic_year }}</td>
                                    <td>{{ $schedule->status === 'published' ? trans('teacher_schedule.published') : trans('teacher_schedule.draft') }}</td>
                                    <td>{{ $schedule->visibility === 'public' ? trans('teacher_schedule.public') : trans('teacher_schedule.authenticated') }}</td>
                                    <td>{{ $schedule->entries_count }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-info" href="{{ route('teacher-schedules.show', $schedule) }}">{{ trans('teacher_schedule.show') }}</a>
                                        <a class="btn btn-sm btn-primary" href="{{ route('teacher-schedules.edit', $schedule) }}">{{ trans('teacher_schedule.edit') }}</a>
                                        <a class="btn btn-sm btn-secondary" target="_blank" href="{{ route('teacher-schedules.print', $schedule) }}">{{ trans('teacher_schedule.print') }}</a>
                                        <a class="btn btn-sm btn-warning" href="{{ route('teacher-schedules.pdf', $schedule) }}">{{ trans('teacher_schedule.pdf') }}</a>
                                        <form class="d-inline" method="POST" action="{{ route('teacher-schedules.destroy', $schedule) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">{{ trans('teacher_schedule.delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted">{{ trans('teacher_schedule.empty') }}</td></tr>
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
