@extends('layoutsadmin.masteradmin')
@section('titlea', trans('timetable.edit'))

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border"><h4 class="box-title">{{ trans('timetable.edit') }}</h4></div>
            <div class="box-body">
                <form method="POST" action="{{ route('timetables.update', $timetable) }}" class="admin-form-panel">
                    @csrf
                    @method('PATCH')
                    <div class="row admin-form-grid">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">{{ trans('timetable.section') }}</label>
                            <select name="section_id" class="form-select" required>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" @selected((int)$section->id === (int)$timetable->section_id)>
                                        {{ $section->classroom->schoolgrade->name_grade ?? '' }} / {{ $section->classroom->name_class ?? '' }} / {{ $section->name_section }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ trans('timetable.academic_year') }}</label>
                            <input type="text" class="form-control" name="academic_year" value="{{ old('academic_year', $timetable->academic_year) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">{{ trans('timetable.title_field') }}</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title', $timetable->title) }}">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input type="hidden" name="is_published" value="0">
                                <input class="form-check-input" type="checkbox" value="1" name="is_published" id="is_published" @checked($timetable->is_published)>
                                <label class="form-check-label" for="is_published">{{ trans('timetable.publish') }}</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-3 admin-section-title">{{ trans('timetable.entries') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered admin-entry-table" id="entries-table">
                            <thead>
                            <tr>
                                <th>{{ trans('timetable.day_range') }}</th>
                                <th>{{ trans('timetable.period') }}</th>
                                <th>{{ trans('timetable.from') }}</th>
                                <th>{{ trans('timetable.to') }}</th>
                                <th>{{ trans('timetable.subject') }}</th>
                                <th>{{ trans('timetable.teacher') }}</th>
                                <th>{{ trans('timetable.room') }}</th>
                                <th>{{ trans('timetable.delete') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($timetable->entries as $i => $entry)
                                <tr>
                                    <td><input class="form-control" type="number" name="entries[{{ $i }}][day_of_week]" min="1" max="7" value="{{ $entry->day_of_week }}" required></td>
                                    <td><input class="form-control" type="number" name="entries[{{ $i }}][period_index]" min="1" max="12" value="{{ $entry->period_index }}" required></td>
                                    <td><input class="form-control" type="time" name="entries[{{ $i }}][starts_at]" value="{{ $entry->starts_at }}"></td>
                                    <td><input class="form-control" type="time" name="entries[{{ $i }}][ends_at]" value="{{ $entry->ends_at }}"></td>
                                    <td><input class="form-control" type="text" name="entries[{{ $i }}][subject_name]" value="{{ $entry->subject_name }}" required></td>
                                    <td>
                                        <select class="form-select" name="entries[{{ $i }}][teacher_id]">
                                            <option value="">--</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" @selected((int)$entry->teacher_id === (int)$teacher->id)>{{ $teacher->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input class="form-control" type="text" name="entries[{{ $i }}][room_name]" value="{{ $entry->room_name }}"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-secondary mb-3" type="button" id="add-entry">{{ trans('timetable.add_entry') }}</button>
                    <div class="admin-form-actions">
                        <button class="btn btn-primary">{{ trans('timetable.update') }}</button>
                        <a href="{{ route('timetables.index') }}" class="btn btn-outline-secondary">{{ trans('timetable.back') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('jsa')
<script>
    (function () {
        let idx = document.querySelectorAll('#entries-table tbody tr').length;
        const addBtn = document.getElementById('add-entry');
        const tbody = document.querySelector('#entries-table tbody');
        if (addBtn && tbody) {
            addBtn.addEventListener('click', function () {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input class="form-control" type="number" name="entries[${idx}][day_of_week]" min="1" max="7" required></td>
                    <td><input class="form-control" type="number" name="entries[${idx}][period_index]" min="1" max="12" required></td>
                    <td><input class="form-control" type="time" name="entries[${idx}][starts_at]"></td>
                    <td><input class="form-control" type="time" name="entries[${idx}][ends_at]"></td>
                    <td><input class="form-control" type="text" name="entries[${idx}][subject_name]" required></td>
                    <td><input class="form-control" type="number" name="entries[${idx}][teacher_id]"></td>
                    <td><input class="form-control" type="text" name="entries[${idx}][room_name]"></td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>`;
                tbody.appendChild(row);
                idx++;
            });
            tbody.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-row')) {
                    e.target.closest('tr').remove();
                }
            });
        }
    })();
</script>
@endsection
