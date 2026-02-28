@extends('layoutsadmin.masteradmin')

@section('titlea')
   {{ __('لوحة المعلم') }}
@stop

@section('contenta')
<div class="row">
    <div class="col-12 col-md-4">
        <div class="box">
            <div class="box-body text-center">
                <div class="avatar avatar-xl bg-primary-light text-primary rounded-circle mb-10 d-inline-flex align-items-center justify-content-center">
                    <i class="mdi mdi-account-school fs-28"></i>
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
@endsection
