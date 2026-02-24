@php
    use App\Models\Chat\ChatRoom;
    use Illuminate\Support\Str;

    $currentUserId = auth()->id();
    $locale = app()->getLocale();

    $translateName = function ($model) use ($locale) {
        if (method_exists($model, 'getTranslation')) {
            return (string) $model->getTranslation('name', $locale);
        }

        $value = $model->name ?? '';

        if (is_array($value)) {
            return (string) ($value[$locale] ?? reset($value) ?? '');
        }

        $decoded = json_decode((string) $value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return (string) ($decoded[$locale] ?? reset($decoded) ?? '');
        }

        return (string) $value;
    };

    $sidebarRooms = collect();
    if ($currentUserId) {
        $sidebarRooms = ChatRoom::query()
            ->forUser($currentUserId)
            ->with([
                'participants:id,name',
                'messages' => fn ($q) => $q->latest()->limit(1)->with('sender:id,name'),
            ])
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();
    }

    $displayRoomName = function ($room) use ($currentUserId, $translateName) {
        if ($room->is_group && $room->name) {
            return $room->name;
        }

        $others = $room->participants
            ->where('id', '!=', $currentUserId)
            ->map(fn ($user) => $translateName($user))
            ->filter();

        if ($others->isNotEmpty()) {
            return $others->implode(', ');
        }

        $first = $room->participants->first();

        return $first ? $translateName($first) : __('محادثة');
    };

    $sidebarRoomsPayload = $sidebarRooms->map(function ($room) use ($displayRoomName, $translateName) {
        $latest = $room->messages->first();

        return [
            'id' => $room->id,
            'display_name' => $displayRoomName($room),
            'initials' => Str::upper(mb_substr($displayRoomName($room), 0, 2)),
            'latest_body' => $latest?->body,
            'latest_at' => optional($latest?->created_at)?->toISOString(),
            'latest_sender' => $latest?->sender ? $translateName($latest->sender) : null,
        ];
    })->values();
@endphp

<!-- Control Sidebar -->
<aside class="control-sidebar">

    <div class="rpanel-title">
        <span class="pull-right btn btn-circle btn-danger">
            <i class="ion ion-close text-white" data-toggle="control-sidebar"></i>
        </span>
    </div>

    <ul class="nav nav-tabs control-sidebar-tabs">
        <li class="nav-item">
            <a href="#control-sidebar-home-tab" data-bs-toggle="tab" class="active">
                <i class="mdi mdi-message-text"></i>
            </a>
        </li>
        <li class="nav-item">
            <a href="#control-sidebar-settings-tab" data-bs-toggle="tab">
                <i class="mdi mdi-playlist-check"></i>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="control-sidebar-home-tab">
            <div class="flexbox">
                <a href="javascript:void(0)" class="text-grey">
                    <i class="ti-more"></i>
                </a>
                <p>{{ __('Users') }}</p>
                <a href="javascript:void(0)" class="text-end text-grey">
                    <i class="ti-plus"></i>
                </a>
            </div>
            <div class="lookup lookup-sm lookup-right d-none d-lg-block">
                <input type="text" name="s" id="sidebar-chat-search" placeholder="{{ __('Search') }}" class="w-p100" autocomplete="off">
            </div>
            <div class="media-list media-list-hover mt-20" id="sidebar-chat-results">
                @forelse ($sidebarRooms as $room)
                    @php
                        $latest = $room->messages->first();
                        $preview = $latest?->body ? \Illuminate\Support\Str::limit($latest->body, 40) : __('لا توجد رسائل بعد');
                        $timestamp = $latest?->created_at ? $latest->created_at->diffForHumans(null, true) : '';
                        $display = $displayRoomName($room);
                    @endphp
                    <div class="media py-10 px-0 align-items-center">
                        <a class="avatar avatar-lg bg-primary text-white d-flex align-items-center justify-content-center" href="{{ route('Chats.index', ['room' => $room->id]) }}">
                            {{ \Illuminate\Support\Str::upper(mb_substr($display, 0, 2)) }}
                        </a>
                        <div class="media-body ms-2">
                            <p class="fs-16 mb-0">
                                <a class="hover-primary" href="{{ route('Chats.index', ['room' => $room->id]) }}">
                                    <strong>{{ $display }}</strong>
                                </a>
                            </p>
                            <p class="mb-0 text-muted">{{ $preview }}</p>
                            <span class="text-muted">{{ $timestamp }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-20">{{ __('لا توجد محادثات حديثة') }}</div>
                @endforelse
            </div>
        </div>

        <div class="tab-pane" id="control-sidebar-settings-tab">
            <div class="flexbox">
                <a href="javascript:void(0)" class="text-grey">
                    <i class="ti-more"></i>
                </a>
                <p>Todo List</p>
                <a href="javascript:void(0)" class="text-end text-grey">
                    <i class="ti-plus"></i>
                </a>
            </div>
            <ul class="todo-list mt-20">
                <li class="py-15 px-5 by-1">
                  <input type="checkbox" id="basic_checkbox_1" class="filled-in">
                  <label for="basic_checkbox_1" class="mb-0 h-15"></label>
                  <span class="text-line">Nulla vitae purus</span>
                  <small class="badge bg-danger"><i class="fa fa-clock-o"></i> 2 mins</small>
                  <div class="tools">
                    <i class="fa fa-edit"></i>
                    <i class="fa fa-trash-o"></i>
                  </div>
                </li>
                <li class="py-15 px-5">
                  <input type="checkbox" id="basic_checkbox_2" class="filled-in">
                  <label for="basic_checkbox_2" class="mb-0 h-15"></label>
                  <span class="text-line">Phasellus interdum</span>
                  <small class="badge bg-info"><i class="fa fa-clock-o"></i> 4 hours</small>
                  <div class="tools">
                    <i class="fa fa-edit"></i>
                    <i class="fa fa-trash-o"></i>
                  </div>
                </li>
                <li class="py-15 px-5 by-1">
                  <input type="checkbox" id="basic_checkbox_3" class="filled-in">
                  <label for="basic_checkbox_3" class="mb-0 h-15"></label>
                  <span class="text-line">Quisque sodales</span>
                  <small class="badge bg-warning"><i class="fa fa-clock-o"></i> 1 day</small>
                  <div class="tools">
                    <i class="fa fa-edit"></i>
                    <i class="fa fa-trash-o"></i>
                  </div>
                </li>
                <li class="py-15 px-5">
                  <input type="checkbox" id="basic_checkbox_4" class="filled-in">
                  <label for="basic_checkbox_4" class="mb-0 h-15"></label>
                  <span class="text-line">Proin nec mi porta</span>
                  <small class="badge bg-success"><i class="fa fa-clock-o"></i> 3 days</small>
                  <div class="tools">
                    <i class="fa fa-edit"></i>
                    <i class="fa fa-trash-o"></i>
                  </div>
                </li>
            </ul>
        </div>
    </div>
</aside>
<!-- /.control-sidebar -->

<!-- Add the sidebar's background. This div must be placed immediately after the control sidebar -->
<div class="control-sidebar-bg"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('sidebar-chat-search');
        const resultsContainer = document.getElementById('sidebar-chat-results');

        if (!searchInput || !resultsContainer) {
            return;
        }

        const data = {
            rooms: @json($sidebarRoomsPayload),
            routes: {
                search: @json(route('chat.users.search')),
                startDirect: @json(route('chat.direct.start')),
                chat: @json(route('Chats.index')),
                csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            },
        };

        const escapeHtml = (unsafe = '') => String(unsafe)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const renderRooms = (rooms) => {
            if (!rooms.length) {
                resultsContainer.innerHTML = `<div class="text-center text-muted py-20">{{ __('لا توجد محادثات حديثة') }}</div>`;
                return;
            }

            resultsContainer.innerHTML = rooms.map(room => `
                <div class="media py-10 px-0 align-items-center">
                    <a class="avatar avatar-lg bg-primary text-white d-flex align-items-center justify-content-center" href="${data.routes.chat}?room=${room.id}">
                        ${escapeHtml((room.initials || '??'))}
                    </a>
                    <div class="media-body ms-2">
                        <p class="fs-16 mb-0">
                            <a class="hover-primary" href="${data.routes.chat}?room=${room.id}">
                                <strong>${escapeHtml(room.display_name || '')}</strong>
                            </a>
                        </p>
                        <p class="mb-0 text-muted">${room.latest_sender ? escapeHtml(room.latest_sender) + ': ' : ''}${room.latest_body ? escapeHtml(room.latest_body).slice(0, 40) : '{{ __('لا توجد رسائل بعد') }}'}</p>
                        <span class="text-muted">${room.latest_at ? new Date(room.latest_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : ''}</span>
                    </div>
                </div>
            `).join('');
        };

        const renderUsers = (users) => {
            if (!users.length) {
                resultsContainer.innerHTML = `<div class="text-center text-muted py-20">{{ __('لا يوجد مستخدمون متاحون') }}</div>`;
                return;
            }

            resultsContainer.innerHTML = users.map(user => `
                <div class="media py-10 px-0 align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-secondary text-white d-flex align-items-center justify-content-center me-2">
                            ${escapeHtml((user.name || '').substring(0, 2).toUpperCase())}
                        </div>
                        <div class="media-body">
                            <p class="fs-16 mb-0"><strong>${escapeHtml(user.name || '')}</strong></p>
                            <small class="text-muted">${escapeHtml(user.email || '')}</small>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm sidebar-start-chat" data-user-id="${user.id}">
                        <i class="mdi mdi-message-plus"></i>
                    </button>
                </div>
            `).join('');
        };

        let searchTimer = null;

        searchInput.addEventListener('input', () => {
            const value = searchInput.value.trim();

            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            if (value === '') {
                renderRooms(data.rooms);
                return;
            }

            searchTimer = setTimeout(async () => {
                try {
                    const response = await fetch(`${data.routes.search}?q=${encodeURIComponent(value)}`, {
                        headers: { 'Accept': 'application/json' },
                    });

                    if (!response.ok) {
                        throw new Error('Search failed');
                    }

                    const payload = await response.json();
                    renderUsers(Array.isArray(payload.users) ? payload.users : []);
                } catch (error) {
                    console.error(error);
                }
            }, 250);
        });

        resultsContainer.addEventListener('click', async (event) => {
            const button = event.target.closest('.sidebar-start-chat');
            if (!button) {
                return;
            }

            const userId = Number(button.dataset.userId);
            if (!userId) {
                return;
            }

            button.disabled = true;
            try {
                const response = await fetch(data.routes.startDirect, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': data.routes.csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId }),
                });

                if (!response.ok) {
                    throw new Error('Cannot start chat');
                }

                const payload = await response.json();
                if (payload?.room?.id) {
                    window.location.href = `${data.routes.chat}?room=${payload.room.id}`;
                }
            } catch (error) {
                console.error(error);
            } finally {
                button.disabled = false;
            }
        });

        renderRooms(data.rooms);
    });
</script>
