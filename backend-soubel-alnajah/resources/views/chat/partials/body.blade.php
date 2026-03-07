@php $locale = app()->getLocale(); @endphp
<div class="row">
    <div class="col-xl-3 col-lg-4">
        <div class="box chat-shell d-flex flex-column">
            <div class="box-header">
                <ul class="nav nav-tabs customtab nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#messages-tab" role="tab">
                            {{ __('Chat') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#contacts-tab" role="tab">
                            {{ __('New') }}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="box-body flex-grow-1">
                <div class="tab-content">
                    <div class="tab-pane active" id="messages-tab" role="tabpanel">
                        <div class="chat-box-one-side media-list-hover" id="rooms-container">
                            @php
                                $currentUserId = auth()->id();
                                $formatRoomName = function ($room) use ($currentUserId, $locale) {
                                    if ($room->is_group && $room->name) {
                                        return $room->name;
                                    }

                                    $nameResolver = function ($user) use ($locale) {
                                        return method_exists($user, 'getTranslation')
                                            ? $user->getTranslation('name', $locale)
                                            : (is_array($user->name) ? ($user->name[$locale] ?? reset($user->name)) : $user->name);
                                    };

                                    $otherUsers = $room->participants
                                        ->where('id', '!=', $currentUserId)
                                        ->map(fn ($user) => $nameResolver($user))
                                        ->filter();

                                    if ($otherUsers->isNotEmpty()) {
                                        return $otherUsers->implode(', ');
                                    }

                                    $first = $room->participants->first();

                                    return $first ? $nameResolver($first) : __('محادثة');
                                };
                            @endphp

                            @forelse ($rooms as $room)
                                @php
                                    $latest = $room->messages->first();
                                    $preview = $latest?->body
                                        ? \Illuminate\Support\Str::limit($latest->body, 50)
                                        : __('لا توجد رسائل بعد');
                                    $roomDisplayName = $formatRoomName($room);
                                @endphp
                                <div class="media chat-room-item rounded {{ $loop->first && !$activeRoomId ? 'active' : '' }}{{ $activeRoomId === $room->id ? ' active' : '' }}"
                                     data-room-id="{{ $room->id }}"
                                     data-room-name="{{ $roomDisplayName }}">
                                    <div class="align-self-center me-2">
                                        <div class="chat-avatar">
                                            {{ mb_substr($roomDisplayName, 0, 2) }}
                                        </div>
                                    </div>
                                    <div class="media-body">
                                        <p class="mb-1 d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold room-name">{{ $roomDisplayName }}</span>
                                            <span class="badge bg-primary-light text-primary rounded-pill">
                                                <i class="mdi mdi-message-text-outline"></i>
                                            </span>
                                        </p>
                            @if($latest)
                                <small class="text-muted d-block mb-1">
                                    {{ optional($latest?->created_at)->diffForHumans(null, true) }}
                                </small>
                                <small class="text-muted room-preview d-block">
                                    {{ $preview }}
                                </small>
                            @else
                                <small class="text-muted room-preview d-block">
                                    {{ __('لا توجد رسائل بعد') }}
                                </small>
                            @endif
                                    </div>
                                </div>
                            @empty
                                <div class="chat-empty-state">
                                    <i class="mdi mdi-account-group-outline text-primary"></i>
                                    <p>{{ __('لا توجد محادثات بعد، ابدأ واحدة الآن') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    <div class="tab-pane" id="contacts-tab" role="tabpanel">
                        <div class="chat-box-one-side media-list-hover" id="contacts-list">
                            @forelse ($availableUsers as $user)
                                @php
                                    $translatedName = method_exists($user, 'getTranslation')
                                        ? $user->getTranslation('name', $locale)
                                        : (is_array($user->name) ? ($user->name[$locale] ?? reset($user->name)) : $user->name);
                                    $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($translatedName ?? $user->name, 0, 2));
                                @endphp
                                <div class="media align-items-center justify-content-between start-direct-chat-row"
                                     data-start-direct="true"
                                     data-user-id="{{ $user->id }}"
                                     role="button">
                                    <div class="d-flex align-items-center">
                                        <div class="chat-avatar me-2 bg-secondary">
                                            {{ $initials }}
                                        </div>
                                        <div class="media-body">
                                            <p class="mb-0 fw-semibold">{{ $translatedName ?? $user->name }}</p>
                                            <small class="text-muted">{{ $user->email }}</small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="chat-empty-state">
                                    <i class="mdi mdi-account-off text-danger"></i>
                                    <p>{{ __('لا يوجد مستخدمون متاحون') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-9 col-lg-8">
        <div class="box chat-shell d-flex flex-column">
            <div class="box-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0" id="active-room-title">{{ __('اختر محادثة') }}</h4>
                    <small class="text-muted d-block" id="active-room-subtitle"></small>
                </div>
                <span class="badge bg-primary d-none" id="active-room-badge">{{ __('مجموعة') }}</span>
            </div>
            <div class="box-body flex-grow-1 d-flex flex-column p-0">
                <div id="chat-messages" class="chat-messages">
                    <div class="chat-empty-state" id="chat-initial-state">
                        <i class="mdi mdi-message-text-outline text-primary"></i>
                        <p>{{ __('ابدأ بمراسلة فريقك الآن') }}</p>
                    </div>
                </div>
                <div class="chat-input-area">
                    <form id="message-form" class="d-flex gap-2 align-items-start">
                        @csrf
                        <div class="flex-grow-1 position-relative">
                            <input type="text" id="message-input" class="form-control" placeholder="{{ __('اكتب رسالتك...') }}" autocomplete="off" disabled>
                            <div class="chat-typing d-none" id="chat-typing-indicator">{{ __('جاري الإرسال...') }}</div>
                        </div>
                        <a href="#" class="btn btn-primary btn-chat-send btn-chat-disabled" id="message-submit" aria-disabled="true">
                            <svg class="send-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 4l6 6h-4v10h-4V10H6l6-6z" fill="currentColor"></path>
                            </svg>
                        </a>
                    </form>
                    <div class="text-danger small mt-2 d-none" id="message-error"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

