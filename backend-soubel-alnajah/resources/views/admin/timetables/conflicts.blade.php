@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('timetable.conflicts.title') }}
@stop
@endsection

@section('contenta')
@php $days = trans('timetable.conflicts.days'); @endphp
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('timetable.conflicts.title') }}</h4>
                <p class="text-muted mb-0">{{ trans('timetable.conflicts.subtitle') }}</p>
            </div>

            <div class="box-body">
                @if ($conflicts->isEmpty())
                    <div class="alert alert-success mb-0"><i class="fa fa-check-circle"></i> {{ trans('timetable.conflicts.none') }}</div>
                @else
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i>
                        {{ trans('timetable.conflicts.alert', ['count' => $conflicts->count()]) }}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('accounts.role') ?? 'النوع' }}</th>
                                    <th>{{ trans('timetable.conflicts.day') }}</th>
                                    <th>{{ trans('timetable.conflicts.period') }}</th>
                                    <th>{{ trans('timetable.conflicts.time') }}</th>
                                    <th></th>
                                    <th>{{ trans('timetable.conflicts.involved') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($conflicts as $i => $c)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>
                                            @if ($c['type'] === 'teacher')
                                                <span class="badge bg-danger">{{ trans('timetable.conflicts.teacher') }}</span>
                                            @else
                                                <span class="badge bg-warning">{{ trans('timetable.conflicts.room') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $days[$c['day']] ?? ('#' . $c['day']) }}</td>
                                        <td>{{ $c['period'] }}</td>
                                        <td dir="ltr">{{ $c['times'] ?? '—' }}</td>
                                        <td class="fw-bold">{{ $c['who'] }}</td>
                                        <td>
                                            @foreach ($c['items'] as $item)
                                                <span class="badge bg-info-light d-inline-block mb-1">
                                                    {{ $item['section'] }}@if(!empty($item['subject'])) — {{ $item['subject'] }}@endif
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="mt-3">
                    <a href="{{ route('timetables.index') }}" class="btn btn-secondary">{{ trans('opt.close') }}</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
