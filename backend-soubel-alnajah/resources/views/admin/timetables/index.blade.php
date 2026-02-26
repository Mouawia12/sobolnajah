@extends('layoutsadmin.masteradmin')
@section('titlea', 'الجداول')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between">
                <h4 class="box-title">الجداول</h4>
                <a href="{{ route('timetables.create') }}" class="btn btn-info">إضافة جدول</a>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="بحث بعنوان الجدول">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="section_id">
                            <option value="">كل الأقسام</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" @selected((string)request('section_id') === (string)$section->id)>
                                    {{ $section->classroom->schoolgrade->name_grade ?? '' }} / {{ $section->classroom->name_class ?? '' }} / {{ $section->name_section }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary" type="submit">تصفية</button>
                        <a class="btn btn-outline-secondary" href="{{ route('timetables.index') }}">إعادة ضبط</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>القسم</th>
                            <th>السنة</th>
                            <th>الحالة</th>
                            <th>الحصص</th>
                            <th>إجراءات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($timetables as $i => $timetable)
                            <tr>
                                <td>{{ $timetables->firstItem() + $i }}</td>
                                <td>{{ $timetable->title ?: 'بدون عنوان' }}</td>
                                <td>{{ $timetable->section->classroom->schoolgrade->name_grade ?? '' }} / {{ $timetable->section->classroom->name_class ?? '' }} / {{ $timetable->section->name_section ?? '' }}</td>
                                <td>{{ $timetable->academic_year }}</td>
                                <td>
                                    <span class="admin-status {{ $timetable->is_published ? 'admin-status-published' : 'admin-status-draft' }}">
                                        {{ $timetable->is_published ? 'منشور' : 'مسودة' }}
                                    </span>
                                </td>
                                <td>{{ $timetable->entries->count() }}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('timetables.edit', $timetable) }}">تعديل</a>
                                    <a class="btn btn-sm btn-info" href="{{ route('timetables.print', $timetable) }}" target="_blank">طباعة</a>
                                    <form class="d-inline" method="POST" action="{{ route('timetables.destroy', $timetable) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد حذف الجدول؟')">حذف</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="admin-empty-state">لا توجد جداول بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $timetables->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
