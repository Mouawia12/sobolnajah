@extends('layoutsadmin.masteradmin')
@section('cssa')
<style>
    .info-box {
        border-radius: 18px;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 110px;
    }

    .info-box-icon {
        font-size: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: rgba(255,255,255,0.2);
    }

    .info-box-icon i {
        font-size: 30px;
        line-height: 1;
    }

    .info-box.bg-light .info-box-icon {
        background: #e0e7ff;
        color: #4338ca;
    }

    .info-box-text {
        font-size: 14px;
        opacity: 0.8;
    }

    .info-box-number {
        font-size: 26px;
        font-weight: 700;
    }

    .info-box .icon-wrapper span[class^="icon-"],
    .info-box .icon-wrapper span[class*=" icon-"] {
        display: inline-block;
    }

    .info-box .light-wrapper span[class^="icon-"],
    .info-box .light-wrapper span[class*=" icon-"] {
        display: inline-block;
    }
</style>

@section('titlea')
   {{ trans('main_header.accueil') }}
@stop
@endsection

@section('contenta')
@php
    $locale = app()->getLocale();
@endphp
<div class="row">
    <div class="col-12">
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box bg-primary text-white shadow-sm">
                    <span class="info-box-icon icon-wrapper"><span class="icon-User"><span class="path1"></span><span class="path2"></span></span></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('إجمالي التلاميذ') }}</span>
                        <span class="info-box-number">{{ number_format($stats['total_students'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box bg-info text-white shadow-sm">
                    <span class="info-box-icon icon-wrapper"><i class="mdi mdi-human-male"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('home.male') }}</span>
                        <span class="info-box-number">{{ number_format($stats['male_students'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box bg-warning text-white shadow-sm">
                    <span class="info-box-icon icon-wrapper"><i class="mdi mdi-human-female"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('home.female') }}</span>
                        <span class="info-box-number">{{ number_format($stats['female_students'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box bg-secondary text-white shadow-sm">
                    <span class="info-box-icon icon-wrapper"><span class="icon-Chat"><span class="path1"></span><span class="path2"></span></span></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('رسائل اليوم') }}</span>
                        <span class="info-box-number">{{ number_format($stats['messages_today'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box bg-danger text-white shadow-sm">
                    <span class="info-box-icon icon-wrapper"><i class="mdi mdi-clipboard-text-outline"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('طلبات التسجيل قيد المراجعة') }}</span>
                        <span class="info-box-number">{{ number_format($stats['pending_inscriptions'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box bg-light shadow-sm">
                    <span class="info-box-icon light-wrapper text-primary"><span class="icon-Mail"><span class="path1"></span><span class="path2"></span></span></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('home.certeficawait') }}</span>
                        <span class="info-box-number text-primary">{{ number_format($notifyunread ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="info-box bg-light shadow-sm">
                    <span class="info-box-icon light-wrapper text-success"><span class="icon-Check"><span class="path1"></span><span class="path2"></span></span></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ trans('home.certeficadone') }}</span>
                        <span class="info-box-number text-success">{{ number_format($notifyread ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12 col-xl-6">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('home.studentnumber') }}</h4>
            </div>
            <div class="box-body analytics-info text-center">
                <div id="students-gender-chart" style="height:360px;"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ __('توزيع التلاميذ حسب المستوى') }}</h4>
            </div>
            <div class="box-body">
                <div id="students-grade-chart" style="height:360px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12 col-xl-7">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ __('التسجيلات الجديدة خلال السنة') }}</h4>
            </div>
            <div class="box-body">
                <div id="students-monthly-chart" style="height:320px;"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ __('آخر التلاميذ المسجلين') }}</h4>
            </div>
            <div class="box-body p-0">
                <ul class="media-list media-list-hover">
                    @forelse ($recentStudents as $student)
                        @php
                            $firstName = $student->getTranslation('prenom', $locale);
                            $lastName = $student->getTranslation('nom', $locale);
                            $fullName = trim($firstName . ' ' . $lastName);
                            $grade = optional(optional(optional($student->section)->classroom)->schoolgrade);
                            $gradeName = $grade ? $grade->getTranslation('name_grade', $locale) : '—';
                            $avatarInitials = mb_strtoupper(mb_substr($fullName ?: __('طالب'), 0, 2));
                        @endphp
                        <li class="media px-20 py-15 align-items-center">
                            <div class="me-15">
                                <span class="avatar avatar-lg rounded bg-primary-light text-primary fw-600 d-flex align-items-center justify-content-center">
                                    {{ $avatarInitials }}
                                </span>
                            </div>
                            <div class="media-body">
                                <p class="mb-0 fw-600">{{ $fullName ?: __('طالب') }}</p>
                                <small class="text-muted">{{ $gradeName }}</small>
                            </div>
                            <div class="text-muted small">
                                {{ optional($student->created_at)->diffForHumans() }}
                            </div>
                        </li>
                    @empty
                        <li class="text-center text-muted py-30">{{ __('لا توجد بيانات متاحة') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('jsa')
<script src="{{ asset('assets/vendor_components/echarts/dist/echarts-en.min.js')}}"></script>
@php
    $genderChartData = [
        ['value' => $stats['female_students'] ?? 0, 'name' => trans('home.female')],
        ['value' => $stats['male_students'] ?? 0, 'name' => trans('home.male')],
    ];
@endphp
<script>
    (function () {
        const genderData = @json($genderChartData);
        const gradeData = @json($studentsByGrade);
        const monthlyData = @json($studentsMonthly);

        const genderChartEl = document.getElementById('students-gender-chart');
        const gradeChartEl = document.getElementById('students-grade-chart');
        const monthlyChartEl = document.getElementById('students-monthly-chart');

        if (genderChartEl) {
            const genderChart = echarts.init(genderChartEl);
            genderChart.setOption({
                tooltip: {
                    trigger: 'item'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left'
                },
                color: ['#ee9ca7', '#38649f'],
                series: [{
                    name: '{{ trans('home.studentnumber') }}',
                    type: 'pie',
                    radius: '70%',
                    data: genderData,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }]
            });
        }

        if (gradeChartEl) {
            const gradeChart = echarts.init(gradeChartEl);
            gradeChart.setOption({
                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    type: 'category',
                    data: gradeData.map(item => item.label),
                    axisLabel: {
                        interval: 0,
                        rotate: 20
                    }
                },
                yAxis: {
                    type: 'value'
                },
                color: ['#4f46e5'],
                series: [{
                    data: gradeData.map(item => item.value),
                    type: 'bar',
                    barWidth: '50%',
                    itemStyle: {
                        borderRadius: [4, 4, 0, 0]
                    }
                }]
            });
        }

        if (monthlyChartEl) {
            const monthlyChart = echarts.init(monthlyChartEl);
            monthlyChart.setOption({
                tooltip: {
                    trigger: 'axis'
                },
                xAxis: {
                    type: 'category',
                    data: monthlyData.map(item => item.label)
                },
                yAxis: {
                    type: 'value'
                },
                color: ['#16a34a'],
                series: [{
                    data: monthlyData.map(item => item.value),
                    type: 'line',
                    smooth: true,
                    areaStyle: {
                        opacity: 0.15
                    }
                }]
            });
        }
    })();
</script>
@endsection
