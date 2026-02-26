@extends('layoutsadmin.masteradmin')
@section('titlea', 'تعديل إعلان توظيف')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">تعديل إعلان توظيف</h4>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('JobPosts.update', $jobPost) }}" class="admin-form-panel">
                    @csrf
                    @method('PATCH')
                    @if($canPickSchool)
                        <div class="mb-3">
                            <label class="form-label">المدرسة</label>
                            <select name="school_id" class="form-select" required>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" @selected((int)$jobPost->school_id === (int)$school->id)>{{ $school->name_school }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $jobPost->title) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" rows="5" class="form-control" required>{{ old('description', $jobPost->description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الشروط</label>
                        <textarea name="requirements" rows="4" class="form-control">{{ old('requirements', $jobPost->requirements) }}</textarea>
                    </div>

                    <div class="row admin-form-grid">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" required>
                                @foreach (['draft', 'published', 'closed'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $jobPost->status) === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">تاريخ النشر</label>
                            <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', optional($jobPost->published_at)->format('Y-m-d\\TH:i')) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">تاريخ الإغلاق</label>
                            <input type="datetime-local" name="closed_at" class="form-control" value="{{ old('closed_at', optional($jobPost->closed_at)->format('Y-m-d\\TH:i')) }}">
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <button class="btn btn-primary">تحديث</button>
                        <a href="{{ route('JobPosts.index') }}" class="btn btn-outline-secondary">رجوع</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
