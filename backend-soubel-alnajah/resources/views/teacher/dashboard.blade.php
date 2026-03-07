@extends('layoutsadmin.masteradmin')

@section('titlea')
   {{ __('لوحة المعلم') }}
@stop

@section('contenta')
<style>
    .profile-photo-wrapper {
        width: 120px;
        height: 120px;
    }
    .profile-photo-preview {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
    }
    .profile-photo-trigger {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 18px rgba(13, 110, 253, 0.35);
    }
</style>
<div class="row">
    <div class="col-12 col-md-4">
        <div class="box">
            <div class="box-body text-center">
                <div class="profile-photo-wrapper position-relative d-inline-block mb-10">
                    <img src="{{ auth()->user()->profile_photo_url ?? asset('/images/avatar/avatar-1.png') }}" class="profile-photo-preview bg-primary-light" alt="user">
                    <button type="button" class="profile-photo-trigger" data-bs-toggle="modal" data-bs-target="#profile-photo-modal-teacher">
                        <i class="fa fa-camera"></i>
                    </button>
                </div>
                <h4 class="mb-5">{{ $teacher?->getTranslation('name', app()->getLocale()) ?? auth()->user()->name }}</h4>
                <p class="text-muted mb-0">{{ optional($teacher?->specialization)->name ?? __('بدون تخصص') }}</p>
                <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="row">
            <div class="col-12 col-sm-4">
                <div class="box bg-primary text-white">
                    <div class="box-body">
                        <h6 class="mb-5">{{ __('الأقسام المسندة') }}</h6>
                        <h2 class="mb-0">{{ $stats['sections_count'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="box bg-success text-white">
                    <div class="box-body">
                        <h6 class="mb-5">{{ __('حصص اليوم') }}</h6>
                        <h2 class="mb-0">{{ $stats['today_periods'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="box bg-info text-white">
                    <div class="box-body">
                        <h6 class="mb-5">{{ __('إجمالي الحصص') }}</h6>
                        <h2 class="mb-0">{{ $stats['total_periods'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="box bg-warning text-white">
                    <div class="box-body">
                        <h6 class="mb-5">{{ __('جداولي الأسبوعية') }}</h6>
                        <h2 class="mb-0">{{ $stats['teacher_schedules_count'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-8">
                <div class="box border border-primary">
                    <div class="box-body d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1">{{ __('جدولي الأسبوعي') }}</h5>
                            <small class="text-muted">{{ __('عرض وطباعة جداولك حسب السنة الدراسية') }}</small>
                        </div>
                        <a href="{{ route('teacher.schedules.index') }}" class="btn btn-primary">{{ __('فتح الجدول') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ __('الأقسام التي أدرسها') }}</h4>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('المستوى') }}</th>
                                <th>{{ __('القسم الرئيسي') }}</th>
                                <th>{{ __('الفوج / الشعبة') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sections as $idx => $section)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $section['grade'] }}</td>
                                    <td>{{ $section['classroom'] }}</td>
                                    <td>{{ $section['section'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">{{ __('لا توجد أقسام مسندة لك حالياً') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profile-photo-modal-teacher" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('profile.photo.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('تحديث الصورة الشخصية') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ __('اختر صورة') }}</label>
                    <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp" class="form-control" required>
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
