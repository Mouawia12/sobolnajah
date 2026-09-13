@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('hr.attendance_report') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ trans('hr.attendance_report') }}</h4>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa fa-print"></i> {{ trans('hr.print') }}</button>
            </div>

            <div class="box-body">
                <form method="GET" class="row g-2 mb-3">
                    @if ($schools->count() > 1)
                        <div class="col-md-3">
                            <select name="branch_id" class="form-select">
                                <option value="">{{ trans('hr.all_branches') }}</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected((string) request('branch_id') === (string) $school->id)>{{ $school->name_school }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2">
                        <label class="form-label mb-0">{{ trans('hr.period_from') }}</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $from }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-0">{{ trans('hr.period_to') }}</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $to }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary" type="submit">{{ trans('hr.filter') }}</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('hr.staff_member') }}</th>
                                <th>{{ trans('hr.job_title') }}</th>
                                <th class="text-success">{{ trans('hr.report_present') }}</th>
                                <th style="color:#f0ad2e;">{{ trans('hr.report_late') }}</th>
                                <th class="text-danger">{{ trans('hr.report_absent') }}</th>
                                <th>{{ trans('hr.report_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $row['name'] }}</td>
                                    <td>{{ $row['subtitle'] }}</td>
                                    <td class="text-success fw-bold">{{ $row['present'] }}</td>
                                    <td style="color:#f0ad2e;" class="fw-bold">{{ $row['late'] }}</td>
                                    <td class="text-danger fw-bold">{{ $row['absent'] }}</td>
                                    <td>{{ $row['total'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted py-4">{{ trans('hr.empty_report') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
