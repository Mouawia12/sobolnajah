@extends('layoutsadmin.masteradmin')
@section('titlea', __('لوحة المحاسب المالي'))

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
                    <button type="button" class="profile-photo-trigger" data-bs-toggle="modal" data-bs-target="#profile-photo-modal-accountant">
                        <i class="fa fa-camera"></i>
                    </button>
                </div>
                <h4 class="mb-5">{{ auth()->user()->name }}</h4>
                <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-3">
        <div class="box bg-primary text-white"><div class="box-body"><h6>{{ __('إجمالي العقود') }}</h6><h3>{{ number_format($stats['contracts_count'] ?? 0) }}</h3></div></div>
    </div>
    <div class="col-12 col-md-3">
        <div class="box bg-success text-white"><div class="box-body"><h6>{{ __('العقود النشطة') }}</h6><h3>{{ number_format($stats['active_contracts'] ?? 0) }}</h3></div></div>
    </div>
    <div class="col-12 col-md-3">
        <div class="box bg-danger text-white"><div class="box-body"><h6>{{ __('العقود المتأخرة') }}</h6><h3>{{ number_format($stats['overdue_contracts'] ?? 0) }}</h3></div></div>
    </div>
    <div class="col-12 col-md-3">
        <div class="box bg-info text-white"><div class="box-body"><h6>{{ __('متحصلات اليوم') }}</h6><h3>{{ number_format($stats['payments_today'] ?? 0, 2) }}</h3></div></div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border d-flex justify-content-between align-items-center">
                <h4 class="box-title">{{ __('آخر الدفعات') }}</h4>
                <a href="{{ route('accounting.payments.index') }}" class="btn btn-sm btn-primary">{{ __('إدارة الدفعات') }}</a>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('رقم الوصل') }}</th>
                                <th>{{ __('التلميذ') }}</th>
                                <th>{{ __('التاريخ') }}</th>
                                <th>{{ __('المبلغ') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $i => $payment)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $payment->receipt_number }}</td>
                                    <td>{{ optional(optional(optional($payment->contract)->student)->user)->name ?? '—' }}</td>
                                    <td>{{ optional($payment->paid_on)->format('Y-m-d') }}</td>
                                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">{{ __('لا توجد دفعات حديثة') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profile-photo-modal-accountant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
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
