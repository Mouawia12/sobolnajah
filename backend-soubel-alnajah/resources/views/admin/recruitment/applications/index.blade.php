@extends('layoutsadmin.masteradmin')
@section('titlea', 'طلبات التوظيف')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">طلبات التوظيف</h4>
            </div>
            <div class="box-body">
                <form method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="بحث: اسم/هاتف/بريد">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">كل الحالات</option>
                            @foreach (['new', 'in_review', 'accepted', 'rejected'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="post_id" class="form-select">
                            <option value="">كل الإعلانات</option>
                            @foreach ($posts as $post)
                                <option value="{{ $post->id }}" @selected((string)request('post_id') === (string)$post->id)>{{ $post->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary" type="submit">تصفية</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>الإعلان</th>
                            <th>المترشح</th>
                            <th>الهاتف</th>
                            <th>البريد</th>
                            <th>الحالة</th>
                            <th>CV</th>
                            <th>تحديث</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($applications as $index => $application)
                            <tr>
                                <td>{{ $applications->firstItem() + $index }}</td>
                                <td>{{ optional($application->post)->title }}</td>
                                <td>{{ $application->full_name }}</td>
                                <td>{{ $application->phone }}</td>
                                <td>{{ $application->email ?? '-' }}</td>
                                <td><span class="admin-status admin-status-{{ $application->status }}">{{ $application->status }}</span></td>
                                <td>
                                    <a href="{{ route('recruitment.applications.cv', $application) }}" class="btn btn-sm btn-info">تحميل</a>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('recruitment.applications.status', $application) }}" class="d-flex gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm">
                                            @foreach (['new', 'in_review', 'accepted', 'rejected'] as $status)
                                                <option value="{{ $status }}" @selected($application->status === $status)>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-primary">حفظ</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="admin-empty-state">لا توجد طلبات.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
