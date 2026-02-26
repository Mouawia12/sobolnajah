@extends('layoutsadmin.masteradmin')
@section('titlea', 'إضافة إعلان توظيف')

@section('contenta')
<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">إضافة إعلان توظيف</h4>
            </div>
            <div class="box-body">
                <form method="POST" action="{{ route('JobPosts.store') }}" class="admin-form-panel">
                    @csrf
                    @if($canPickSchool)
                        <div class="mb-3">
                            <label class="form-label">المدرسة</label>
                            <select name="school_id" class="form-select" required>
                                <option value="">اختر مدرسة</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name_school }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">العنوان</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" rows="5" class="form-control" required>{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الشروط</label>
                        <textarea name="requirements" rows="4" class="form-control">{{ old('requirements') }}</textarea>
                    </div>

                    <div class="row admin-form-grid">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" @selected(old('status') === 'draft')>مسودة</option>
                                <option value="published" @selected(old('status') === 'published')>منشور</option>
                                <option value="closed" @selected(old('status') === 'closed')>مغلق</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">تاريخ النشر</label>
                            <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">تاريخ الإغلاق</label>
                            <input type="datetime-local" name="closed_at" class="form-control" value="{{ old('closed_at') }}">
                        </div>
                    </div>

                    <div class="admin-form-actions">
                        <button class="btn btn-primary">حفظ</button>
                        <a href="{{ route('JobPosts.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
