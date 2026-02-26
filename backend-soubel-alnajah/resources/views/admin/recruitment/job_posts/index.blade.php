@extends('layoutsadmin.masteradmin')
@section('titlea', 'إعلانات التوظيف')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between">
                <h4 class="box-title">إعلانات التوظيف</h4>
                <a href="{{ route('JobPosts.create') }}" class="btn btn-info">إضافة إعلان</a>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="بحث بعنوان الإعلان">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach (['draft' => 'مسودة', 'published' => 'منشور', 'closed' => 'مغلق'] as $key => $label)
                                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary" type="submit">تصفية</button>
                        <a href="{{ route('JobPosts.index') }}" class="btn btn-outline-secondary">إعادة ضبط</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>العنوان</th>
                            <th>الحالة</th>
                            <th>عدد الطلبات</th>
                            <th>تاريخ النشر</th>
                            <th>الإجراءات</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($jobPosts as $index => $jobPost)
                            <tr>
                                <td>{{ $jobPosts->firstItem() + $index }}</td>
                                <td>{{ $jobPost->title }}</td>
                                <td><span class="admin-status admin-status-{{ $jobPost->status }}">{{ $jobPost->status }}</span></td>
                                <td>{{ $jobPost->applications_count }}</td>
                                <td>{{ $jobPost->published_at ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('JobPosts.edit', $jobPost) }}" class="btn btn-sm btn-primary">تعديل</a>
                                    <form method="POST" action="{{ route('JobPosts.destroy', $jobPost) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('تأكيد حذف الإعلان؟')">حذف</button>
                                    </form>
                                    <a href="{{ route('recruitment.applications.index', ['post_id' => $jobPost->id]) }}" class="btn btn-sm btn-info">الطلبات</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="admin-empty-state">لا توجد إعلانات توظيف بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $jobPosts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
