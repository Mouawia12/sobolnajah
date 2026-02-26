@php
    $title = trim(strip_tags($__env->yieldContent('titlea')));
    $trail = $breadcrumbs ?? [];
@endphp

@if ($title !== '' || count($trail))
    <div class="content-header mb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h3 class="mb-2 mb-md-0">{{ $title !== '' ? $title : 'Dashboard' }}</h3>
        @if (count($trail))
            <ol class="breadcrumb mb-0">
                @foreach ($trail as $crumb)
                    @if (!empty($crumb['url']))
                        <li class="breadcrumb-item"><a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a></li>
                    @else
                        <li class="breadcrumb-item active">{{ $crumb['label'] }}</li>
                    @endif
                @endforeach
            </ol>
        @endif
    </div>
@endif
