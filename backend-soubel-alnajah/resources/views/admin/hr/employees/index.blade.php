@extends('layoutsadmin.masteradmin')
@section('cssa')
@section('titlea')
    {{ trans('hr.employees') }}
@stop
@endsection

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ trans('hr.employees') }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('employees.print', request()->only('q','branch_id')) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="fa fa-print"></i> {{ trans('print.print') }}
                    </a>
                    <a href="{{ route('staff-attendance.record') }}" class="btn btn-success">
                        <i class="fa fa-calendar-check-o"></i> {{ trans('hr.staff_attendance') }}
                    </a>
                    <a href="javascript:void(0);" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add-employee">
                        <i class="fa fa-plus"></i> {{ trans('hr.add_employee') }}
                    </a>
                </div>
            </div>

            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                            placeholder="{{ trans('hr.search_staff') }}">
                    </div>
                    @if ($schools->count() > 1)
                        <div class="col-md-3">
                            <select name="branch_id" class="form-select" onchange="this.form.submit()">
                                <option value="">{{ trans('hr.all_branches') }}</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}" @selected((string) request('branch_id') === (string) $school->id)>
                                        {{ $school->name_school }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-2 d-flex gap-1">
                        <button class="btn btn-primary" type="submit">{{ trans('hr.filter') }}</button>
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">{{ trans('opt.close') }}</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ trans('hr.name') }}</th>
                                <th>{{ trans('hr.job_title') }}</th>
                                <th>{{ trans('hr.phone') }}</th>
                                <th>{{ trans('hr.joining_date') }}</th>
                                <th>{{ trans('hr.is_active') }}</th>
                                <th>{{ trans('hr.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($Employees as $index => $employee)
                                <tr>
                                    <td>{{ $Employees->firstItem() + $index }}</td>
                                    <td class="fw-bold">{{ $employee->name }}
                                        @if ($employee->user)
                                            <span class="badge badge-info-light d-block mt-1">{{ $employee->user->email }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $employee->job_title ?: '—' }}</td>
                                    <td dir="ltr">{{ $employee->phone ?: '—' }}</td>
                                    <td>{{ $employee->joining_date?->format('Y-m-d') ?: '—' }}</td>
                                    <td>
                                        @if ($employee->is_active)
                                            <span class="badge bg-success">{{ trans('hr.active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ trans('hr.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="javascript:void(0);" class="btn btn-primary-light btn-circle"
                                            data-bs-toggle="modal" data-bs-target="#modal-edit-employee-{{ $employee->id }}"
                                            title="{{ trans('hr.edit_employee') }}"><i class="fa fa-pencil"></i></a>
                                        <a href="javascript:void(0);" class="btn btn-danger-light btn-circle"
                                            data-bs-toggle="modal" data-bs-target="#modal-del-employee-{{ $employee->id }}"
                                            title="{{ trans('opt.delete2') }}"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>

                                {{-- مودال تعديل --}}
                                <div class="modal fade" id="modal-edit-employee-{{ $employee->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('employees.update', $employee->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ trans('hr.edit_employee') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    @include('admin.hr.employees._fields', ['employee' => $employee])
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ trans('opt.save') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- مودال حذف --}}
                                <div class="modal fade" id="modal-del-employee-{{ $employee->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('employees.destroy', $employee->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-body">
                                                    <h5 class="text-danger">{{ trans('opt.deletemsg') }}</h5>
                                                    <p class="mb-0">{{ $employee->name }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button>
                                                    <button type="submit" class="btn btn-danger">{{ trans('opt.delete2') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr><td colspan="7" class="text-muted py-4">{{ trans('hr.no_staff') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $Employees->links() }}
            </div>
        </div>
    </div>
</div>

{{-- مودال إضافة --}}
<div class="modal fade" id="modal-add-employee" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('employees.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('hr.add_employee') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    @if ($schools->count() > 1)
                        <div class="mb-3">
                            <label class="form-label">{{ trans('hr.school') }}</label>
                            <select name="school_id" class="form-select" required>
                                <option value="" disabled selected>{{ trans('hr.choose_branch') }}</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name_school }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @include('admin.hr.employees._fields', ['employee' => null])

                    <hr>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="create_account" value="1" id="create_account">
                        <label class="form-check-label" for="create_account">{{ trans('hr.create_account') }}</label>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">{{ trans('hr.email') }}</label>
                        <input type="email" name="email" class="form-control" dir="ltr">
                        <small class="text-muted">{{ trans('hr.account_hint') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('opt.close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('opt.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
