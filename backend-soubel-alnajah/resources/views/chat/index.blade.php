@extends('layoutsadmin.masteradmin')

@section('titlea')
    {{ trans('opt.chat_users') }}
@stop

@section('cssa')
<style>
    .chat-shell {
        min-height: 70vh;
    }

    .chat-box-one-side {
        max-height: 65vh;
        overflow-y: auto;
    }

    .chat-room-item {
        cursor: pointer;
        transition: background-color 0.25s ease;
        border-radius: 12px;
        padding: 0.65rem 0.4rem;
    }

    .chat-room-item.active,
    .chat-room-item:hover {
        background: rgba(99, 102, 241, 0.08);
    }

    .chat-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #4f46e5;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
    }

    .chat-messages {
        height: 60vh;
        overflow-y: auto;
        padding: 1rem;
        background: #f9fafb;
    }

    .chat-message-wrapper {
        max-width: 75%;
        display: flex;
        flex-direction: column;
    }

    .chat-message-card {
        width: 100%;
    }

    .chat-message-card.me {
        margin-left: auto;
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #fff;
    }

    .chat-message-card.them {
        background: #ffffff;
        border: 1px solid #e5e7eb;
    }

    .message-time {
        font-size: 12px;
        margin-top: 4px;
        display: inline-block;
        color: #475569;
    }
    .message-time.text-white-50,
    .message-time.text-white {
        color: #cbd5f5 !important;
    }

    .chat-empty-state {
        text-align: center;
        color: #9ca3af;
        padding: 40px 10px;
    }

    .chat-empty-state i {
        font-size: 42px;
        display: block;
        margin-bottom: 12px;
    }

    .chat-input-area {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 1rem;
    }

    .chat-typing {
        font-size: 13px;
        color: #6b7280;
    }

    .media-list-hover .media {
        padding: 0.65rem 0.35rem;
    }

    .media-list-hover .media:not(:last-child) {
        border-bottom: 1px solid #f1f5f9;
    }

    .start-direct-chat-row {
        cursor: pointer;
        transition: background-color 0.25s ease;
        border-radius: 12px;
        padding: 0.75rem 0.35rem;
    }

    .start-direct-chat-row:hover,
    .start-direct-chat-row:focus {
        background: rgba(79, 70, 229, 0.08);
    }

    .btn-chat-send.btn-chat-disabled {
        pointer-events: none;
        opacity: 0.5;
    }
</style>
@endsection

@section('contenta')
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
                            <i class="mdi mdi-send" @if(app()->getLocale() === 'ar') style="transform: scaleX(-1);" @endif></i>
                        </a>
                    </form>
                    <div class="text-danger small mt-2 d-none" id="message-error"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('jsa')
<script>
    (function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const currentUserId = {{ auth()->id() }};
        let rooms = @json($roomsJson);
        rooms = Array.isArray(rooms) ? rooms : [];
        let activeRoomId = {{ $activeRoomId ?? 'null' }};
        let isLoadingMessages = false;
        let roomsPollInterval = null;
        let messagesPollInterval = null;
        let lastMessageFingerprint = null;

        const roomsContainer = document.getElementById('rooms-container');
        const messagesContainer = document.getElementById('chat-messages');
        const contactsListEl = document.getElementById('contacts-list');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const messageSubmit = document.getElementById('message-submit');
        const messageError = document.getElementById('message-error');
        const typingIndicator = document.getElementById('chat-typing-indicator');
        const activeRoomTitle = document.getElementById('active-room-title');
        const activeRoomSubtitle = document.getElementById('active-room-subtitle');
        const activeRoomBadge = document.getElementById('active-room-badge');
        const initialState = document.getElementById('chat-initial-state');

        const isRtl = @json(app()->getLocale() === 'ar');

        const escapeHtml = (unsafe = '') => String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(//g, '')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const nl2br = (input = '') => escapeHtml(input).replace(/\n/g, '<br>');

        const routeTemplates = {
            listRooms: @json(route('chat.rooms.list')),
            messages: @json(route('chat.rooms.messages', ['room' => '___ROOM___'])),
            sendMessage: @json(route('chat.rooms.messages.send', ['room' => '___ROOM___'])),
            markAsRead: @json(route('chat.rooms.read', ['room' => '___ROOM___'])),
            startDirect: @json(route('chat.direct.start')),
            chatIndex: @json(route('Chats.index')),
        };

        const routes = {
            listRooms: routeTemplates.listRooms,
            messages: (roomId) => routeTemplates.messages.replace('___ROOM___', roomId),
            sendMessage: (roomId) => routeTemplates.sendMessage.replace('___ROOM___', roomId),
            markAsRead: (roomId) => routeTemplates.markAsRead.replace('___ROOM___', roomId),
            startDirect: routeTemplates.startDirect,
            chatIndex: routeTemplates.chatIndex,
        };

        const formatRoomName = (room) => {
            if (room.display_name) {
                return room.display_name;
            }

            if (room.is_group && room.name) {
                return room.name;
            }

            const otherUsers = (room.participants || []).filter((user) => user.id !== currentUserId);
            if (otherUsers.length) {
                return otherUsers.map(user => user.name).join(', ');
            }

            return room.participants?.[0]?.name || 'محادثة';
        };

        const renderRooms = () => {
            if (!rooms.length) {
                roomsContainer.innerHTML = `
                    <div class="chat-empty-state">
                        <i class="mdi mdi-account-group-outline text-primary"></i>
                        <p>{{ __('لا توجد محادثات بعد، ابدأ واحدة الآن') }}</p>
                    </div>
                `;
                return;
            }

            roomsContainer.innerHTML = rooms.map(room => {
                const latest = Array.isArray(room.messages) ? room.messages[0] ?? null : null;
                const preview = latest
                    ? `${latest.sender?.id === currentUserId ? '{{ __('أنت') }}' : latest.sender?.name || '{{ __('مستخدم') }}'}: ${escapeHtml(latest.body).slice(0, 60)}`
                    : '{{ __('لا توجد رسائل بعد') }}';
                const isActive = room.id === activeRoomId;
                const timestamp = latest && latest.created_at ? new Date(latest.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                const displayName = formatRoomName(room);
                const initials = displayName ? displayName.substring(0, 2).toUpperCase() : '??';

                return `
                    <div class="media chat-room-item ${isActive ? 'active' : ''}" data-room-id="${room.id}">
                        <div class="align-self-center me-2">
                            <div class="chat-avatar">
                                ${escapeHtml(initials)}
                            </div>
                        </div>
                        <div class="media-body">
                            <p class="mb-1 d-flex justify-content-between align-items-center">
                                <span class="fw-semibold room-name">${escapeHtml(displayName)}</span>
                                <span class="badge bg-primary-light text-primary rounded-pill">
                                    <i class="mdi mdi-message-text-outline"></i>
                                </span>
                            </p>
                            <small class="text-muted d-block mb-1">${timestamp}</small>
                            <small class="text-muted room-preview d-block">${preview}</small>
                        </div>
                    </div>
                `;
            }).join('');
        };

        const renderMessages = (messages) => {
            if (!messages.length) {
                messagesContainer.innerHTML = `
                    <div class="chat-empty-state">
                        <i class="mdi mdi-forum-outline text-primary"></i>
                        <p>{{ __('ابدأ المحادثة الآن') }}</p>
                    </div>
                `;
                return;
            }

            messagesContainer.innerHTML = messages.map(message => {
                const isMe = message.user_id === currentUserId;
                const timestamp = message.created_at ? new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '';
                const bodyHtml = nl2br(message.body ?? '');

                const wrapperJustify = isRtl
                    ? (isMe ? 'justify-content-start' : 'justify-content-end')
                    : (isMe ? 'justify-content-end' : 'justify-content-start');

                const bubbleAlignment = isRtl
                    ? (isMe ? 'align-items-start' : 'align-items-end')
                    : (isMe ? 'align-items-end' : 'align-items-start');

                const timeClass = isRtl
                    ? (isMe ? 'text-white text-start' : 'text-muted text-end')
                    : (isMe ? 'text-white-50 text-end' : 'text-muted text-start');

                return `
                    <div class="d-flex mb-3 ${wrapperJustify}">
                        <div class="chat-message-wrapper ${bubbleAlignment}">
                            <div class="chat-message-card p-3 rounded shadow-sm ${isMe ? 'me' : 'them'}">
                                <div>${bodyHtml}</div>
                            </div>
                            ${timestamp ? `<small class="message-time ${timeClass}">${escapeHtml(timestamp)}</small>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        };

        const refreshRooms = async () => {
            try {
                const response = await fetch(routes.listRooms, {
                    headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' }
                });
                if (!response.ok) return;
                rooms = await response.json();
                rooms = Array.isArray(rooms) ? rooms : [];
                renderRooms();
            } catch (error) {
                console.error(error);
            }
        };

        const refreshMessages = async (forceScroll = false) => {
            if (!activeRoomId || isLoadingMessages) return;
            isLoadingMessages = true;

            try {
                const response = await fetch(routes.messages(activeRoomId), {
                    headers: { 'Accept': 'application/json', 'Cache-Control': 'no-cache' }
                });
                if (!response.ok) throw new Error('Failed to load messages');
                const messages = await response.json();

                const fingerprint = JSON.stringify(messages.map(msg => msg.id));
                if (fingerprint !== lastMessageFingerprint || forceScroll) {
                    renderMessages(messages);
                    lastMessageFingerprint = fingerprint;
                }
            } catch (error) {
                console.error(error);
            } finally {
                isLoadingMessages = false;
            }
        };

        const activateRoom = async (roomId, options = { scroll: true }) => {
            if (!roomId) return;
            activeRoomId = Number(roomId);
            messageInput.disabled = false;
            if (messageSubmit) {
                messageSubmit.classList.remove('btn-chat-disabled');
                messageSubmit.setAttribute('aria-disabled', 'false');
            }
            messageInput.focus();
            initialState?.classList.add('d-none');

            const room = rooms.find(r => r.id === activeRoomId);
            if (room) {
                activeRoomTitle.textContent = formatRoomName(room);
                const otherUsers = (room.participants || []).filter(user => user.id !== currentUserId).map(user => user.name);
                activeRoomSubtitle.textContent = otherUsers.join(', ');
                activeRoomBadge.classList.toggle('d-none', !room.is_group);
            }

            await fetch(routes.markAsRead(activeRoomId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            }).catch(() => {});

            await refreshMessages(options.scroll);
            renderRooms();
            restartMessagesPolling();
        };

        const restartRoomsPolling = () => {
            if (roomsPollInterval) clearInterval(roomsPollInterval);
            roomsPollInterval = setInterval(refreshRooms, 7000);
        };

        const restartMessagesPolling = () => {
            if (messagesPollInterval) clearInterval(messagesPollInterval);
            messagesPollInterval = setInterval(refreshMessages, 4000);
        };

        roomsContainer?.addEventListener('click', (event) => {
            const item = event.target.closest('.chat-room-item');
            if (!item) return;
            const roomId = item.getAttribute('data-room-id');
            activateRoom(roomId);
        });

        contactsListEl?.addEventListener('click', async (event) => {
            const row = event.target.closest('.start-direct-chat-row[data-start-direct="true"]');
            if (!row) return;

            const userId = Number(row.getAttribute('data-user-id'));
            if (!userId) return;

            row.classList.add('disabled');
            try {
                const response = await fetch(routes.startDirect, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                if (!response.ok) throw new Error('failed');
                const { room } = await response.json();
                await refreshRooms();
                await activateRoom(room.id);
                const tabTrigger = document.querySelector('a[href="#messages-tab"]');
                if (tabTrigger && typeof bootstrap !== 'undefined') {
                    new bootstrap.Tab(tabTrigger).show();
                }
            } catch (error) {
                console.error(error);
            } finally {
                row.classList.remove('disabled');
            }
        });

        const sendCurrentMessage = async () => {
            if (!activeRoomId) return;

            const body = messageInput.value.trim();
            if (!body) return;

            messageInput.disabled = true;
            if (messageSubmit) {
                messageSubmit.classList.add('btn-chat-disabled');
                messageSubmit.setAttribute('aria-disabled', 'true');
            }
            typingIndicator.classList.remove('d-none');
            messageError.classList.add('d-none');

            try {
                const response = await fetch(routes.sendMessage(activeRoomId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ body })
                });

                if (!response.ok) throw new Error('Failed to send');
                await response.json();
                messageInput.value = '';
                typingIndicator.classList.add('d-none');

                await refreshMessages(true);
                await refreshRooms();
            } catch (error) {
                console.error(error);
                messageError.textContent = '{{ __('تعذر إرسال الرسالة، حاول مجدداً') }}';
                messageError.classList.remove('d-none');
            } finally {
                messageInput.disabled = false;
                if (messageSubmit) {
                    messageSubmit.classList.remove('btn-chat-disabled');
                    messageSubmit.setAttribute('aria-disabled', 'false');
                }
                typingIndicator.classList.add('d-none');
                messageInput.focus();
            }
        };

        if (messageForm) {
            messageForm.addEventListener('submit', (event) => {
                event.preventDefault();
                sendCurrentMessage();
            });

            if (messageSubmit) {
                messageSubmit.addEventListener('click', (event) => {
                    event.preventDefault();
                    if (messageSubmit.classList.contains('btn-chat-disabled')) {
                        return;
                    }
                    sendCurrentMessage();
                });
            }

            if (messageInput) {
                messageInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        if (!messageSubmit || messageSubmit.classList.contains('btn-chat-disabled')) {
                            return;
                        }
                        sendCurrentMessage();
                    }
                });
            }
        }

        window.addEventListener('beforeunload', () => {
            if (roomsPollInterval) clearInterval(roomsPollInterval);
            if (messagesPollInterval) clearInterval(messagesPollInterval);
        });

        renderRooms();
        restartRoomsPolling();

        if (rooms.length) {
            const roomToLoad = activeRoomId || rooms[0].id;
            activateRoom(roomToLoad, { scroll: true });
        }
    })();
</script>
@endsection
