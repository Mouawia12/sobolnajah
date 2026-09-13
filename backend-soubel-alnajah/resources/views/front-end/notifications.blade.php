@extends('layouts.masterhome')

@section('title')
    الإشعارات
@endsection

@section('content')
<section class="bg-img pt-150 pb-20" data-overlay="1" style="background-image: url({{ asset('images/logincover.jpg') }});">
    <div class="container">
        <div class="row"><div class="col-12"><div class="text-center">
            <h2 class="page-title text-white">الإشعارات</h2>
        </div></div></div>
    </div>
</section>

<section class="py-50">
    <div class="container">
        <div class="box">
            <div class="box-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-20">
                    <h4 class="mb-0">صندوق الإشعارات
                        @if($unreadCount > 0)<span class="badge bg-danger">{{ $unreadCount }}</span>@endif
                    </h4>
                    <div class="d-flex gap-2">
                        @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.read_all') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">تعليم الكل كمقروء</button>
                            </form>
                        @endif
                        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">العودة للوحة</a>
                    </div>
                </div>

                @forelse($notifications as $n)
                    @php
                        $d = $n->data;
                        $isMarks = ($d['kind'] ?? null) === 'marks_entered';
                        $link = $isMarks && !empty($d['student_id']) ? route('reports.bulletin', $d['student_id']) : null;
                    @endphp
                    <div class="d-flex align-items-start gap-3 p-3 mb-2 rounded"
                        style="background: {{ $n->read_at ? '#f6f7f9' : '#eef4fb' }}; border:1px solid #e6eaf0;">
                        <div class="flex-grow-1">
                            @if($isMarks)
                                <div class="fw-bold">📝 تم إدخال نقاط جديدة</div>
                                <div class="text-muted">
                                    {{ $d['subject'] ?? '' }}@if(!empty($d['title'])) — {{ $d['title'] }}@endif
                                </div>
                            @else
                                <div class="fw-bold">🔔 إشعار</div>
                            @endif
                            <small class="text-muted">{{ optional($n->created_at)->diffForHumans() }}</small>
                        </div>
                        <div class="d-flex flex-column gap-1">
                            @if($link)
                                <a href="{{ $link }}" class="btn btn-primary btn-sm" target="_blank">عرض الكشف</a>
                            @endif
                            @unless($n->read_at)
                                <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-light btn-sm w-100">تعليم كمقروء</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info mb-0">لا توجد إشعارات.</div>
                @endforelse

                <div class="mt-3">{{ $notifications->links() }}</div>
            </div>
        </div>
    </div>
</section>
@endsection
