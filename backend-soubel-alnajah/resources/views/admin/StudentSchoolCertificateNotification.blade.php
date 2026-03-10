@extends('layoutsadmin.masteradmin')

@section('titlea')
   {{ trans('student.certificate_modal_title') }}
@stop

@section('contenta')
@php
    $requestData = is_string($arryear) ? (json_decode($arryear, true) ?: []) : (is_array($arryear) ? $arryear : []);
    $purposeLabels = [
        'enrollment' => trans('student.certificate_purpose_enrollment'),
        'scholarship' => trans('student.certificate_purpose_scholarship'),
        'administrative' => trans('student.certificate_purpose_administrative'),
        'other' => trans('student.certificate_purpose_other'),
    ];
    $deliveryLabels = [
        'printed' => trans('student.certificate_delivery_printed'),
        'digital' => trans('student.certificate_delivery_digital'),
    ];
    $langLabels = [
        'ar' => 'العربية',
        'fr' => 'Français',
        'en' => 'English',
    ];
    $fallbackName = app()->isLocale('fr')
        ? ($requestData['namefr'] ?? ($requestData['namear'] ?? trans('student.name')))
        : ($requestData['namear'] ?? ($requestData['namefr'] ?? trans('student.name')));
@endphp

<div class="row">
    <div class="col-12 col-xl-4">
        <div class="box">
            <div class="box-body text-center">
                <img src="{{ optional($StudentInfo?->user)->profile_photo_url ?? asset('images/avatar/avatar-12.png') }}" width="120" class="rounded-circle bg-info-light mb-15" alt="user" />
                <h4 class="mb-5">{{ $StudentInfo ? ($StudentInfo->prenom . ' ' . $StudentInfo->nom) : $fallbackName }}</h4>
                <p class="text-muted mb-10">{{ $StudentInfo?->user->email ?? ($requestData['email'] ?? '—') }}</p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <span class="badge badge-pill badge-info-light fs-14">{{ $StudentInfo ? ('0' . $StudentInfo->numtelephone) : '—' }}</span>
                    <span class="badge badge-pill badge-primary-light fs-14">{{ $StudentInfo?->section?->classroom?->name_class ?? '—' }}</span>
                    <span class="badge badge-pill badge-warning-light fs-14">{{ $StudentInfo?->section?->name_section ?? '—' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title">{{ trans('student.certificate_modal_title') }}</h4>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">{{ trans('student.certificate_year') }}</label>
                        <div class="fw-semibold">{{ $requestData['year'] ?? '—' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">{{ trans('student.certificate_purpose') }}</label>
                        <div class="fw-semibold">{{ $purposeLabels[$requestData['purpose'] ?? ''] ?? trans('student.certificate_purpose_other') }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">{{ trans('student.certificate_copies') }}</label>
                        <div class="fw-semibold">{{ (int) ($requestData['copies'] ?? 1) }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">{{ trans('student.certificate_language') }}</label>
                        <div class="fw-semibold">{{ $langLabels[$requestData['preferred_language'] ?? ''] ?? '—' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">{{ trans('student.certificate_delivery') }}</label>
                        <div class="fw-semibold">{{ $deliveryLabels[$requestData['delivery_method'] ?? ''] ?? '—' }}</div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">{{ trans('student.certificate_notes') }}</label>
                        <div class="p-10 bg-light rounded">{{ ($requestData['notes'] ?? null) ?: '—' }}</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">{{ __('تاريخ الطلب') }}</label>
                        <div class="fw-semibold">{{ $requestData['requested_at'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
