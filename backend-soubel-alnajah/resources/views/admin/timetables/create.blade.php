@extends('layoutsadmin.masteradmin')
@section('titlea', 'إضافة جدول')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border"><h4 class="box-title">إضافة جدول</h4></div>
            <div class="box-body">
                <form method="POST" action="{{ route('timetables.store') }}" class="admin-form-panel">
                    @csrf
                    <div class="row admin-form-grid">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">القسم</label>
                            <select name="section_id" class="form-select" required>
                                <option value="">اختر القسم</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->classroom->schoolgrade->name_grade ?? '' }} / {{ $section->classroom->name_class ?? '' }} / {{ $section->name_section }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">السنة الدراسية</label>
                            <input type="text" class="form-control" name="academic_year" placeholder="2026-2027" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">العنوان</label>
                            <input type="text" class="form-control" name="title">
                        </div>
                        <div class="col-md-2 mb-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input type="hidden" name="is_published" value="0">
                                <input class="form-check-input" type="checkbox" value="1" name="is_published" id="is_published">
                                <label class="form-check-label" for="is_published">نشر</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-3 admin-section-title">الحصص</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered admin-entry-table" id="entries-table">
                            <thead>
                            <tr>
                                <th>اليوم (1-7)</th>
                                <th>الحصة</th>
                                <th>من</th>
                                <th>إلى</th>
                                <th>المادة</th>
                                <th>الأستاذ</th>
                                <th>القسم/القاعة</th>
                                <th>حذف</th>
                            </tr>
                            </thead>
                            <tbody>
                            @for($i = 0; $i < 3; $i++)
                                <tr>
                                    <td><input class="form-control" type="number" name="entries[{{ $i }}][day_of_week]" min="1" max="7" required></td>
                                    <td><input class="form-control" type="number" name="entries[{{ $i }}][period_index]" min="1" max="12" required></td>
                                    <td><input class="form-control" type="time" name="entries[{{ $i }}][starts_at]"></td>
                                    <td><input class="form-control" type="time" name="entries[{{ $i }}][ends_at]"></td>
                                    <td><input class="form-control" type="text" name="entries[{{ $i }}][subject_name]" required></td>
                                    <td>
                                        <select class="form-select" name="entries[{{ $i }}][teacher_id]">
                                            <option value="">--</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input class="form-control" type="text" name="entries[{{ $i }}][room_name]"></td>
                                    <td><button type="button" class="btn btn-sm btn-danger remove-row">x</button></td>
                                </tr>
                            @endfor
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-secondary mb-3" type="button" id="add-entry">إضافة حصة</button>
                    <div class="admin-form-actions">
                        <button class="btn btn-primary">حفظ</button>
                        <a href="{{ route('timetables.index') }}" class="btn btn-outline-secondary">رجوع</a>
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
