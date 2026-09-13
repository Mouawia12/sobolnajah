<div class="mb-3">
    <label class="form-label">{{ trans('hr.name') }} <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required value="{{ old('name', $employee->name ?? '') }}">
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ trans('hr.job_title') }}</label>
        <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $employee->job_title ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ trans('hr.phone') }}</label>
        <input type="text" name="phone" class="form-control" dir="ltr" value="{{ old('phone', $employee->phone ?? '') }}">
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ trans('hr.joining_date') }}</label>
        <input type="date" name="joining_date" class="form-control"
            value="{{ old('joining_date', isset($employee) && $employee?->joining_date ? $employee->joining_date->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-6 mb-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_{{ $employee->id ?? 'new' }}"
                @checked(old('is_active', $employee->is_active ?? true))>
            <label class="form-check-label" for="is_active_{{ $employee->id ?? 'new' }}">{{ trans('hr.is_active') }}</label>
        </div>
    </div>
</div>
<div class="mb-2">
    <label class="form-label">{{ trans('hr.address') }}</label>
    <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->address ?? '') }}</textarea>
</div>
