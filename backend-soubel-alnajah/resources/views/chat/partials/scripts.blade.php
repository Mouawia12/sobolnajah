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
        const contactsSearchInput = document.getElementById('contacts-search-input');
        const contactsSearchEmpty = document.getElementById('contacts-search-empty');
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

        const renderAvatar = (avatarUrl, fallbackText) => {
            if (avatarUrl) {
                return `<img src="${escapeHtml(avatarUrl)}" alt="${escapeHtml(fallbackText || 'avatar')}">`;
            }

            return escapeHtml(fallbackText || '??');
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
                const avatarHtml = renderAvatar(room.avatar_url || null, initials);

                return `
                    <div class="media chat-room-item ${isActive ? 'active' : ''}" data-room-id="${room.id}">
                        <div class="align-self-center me-2">
                            <div class="chat-avatar">
                                ${avatarHtml}
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

        const normalizeSearch = (value = '') => String(value).toLocaleLowerCase().trim();

        const filterContacts = () => {
            if (!contactsListEl) return;

            const query = normalizeSearch(contactsSearchInput?.value || '');
            const rows = Array.from(
                contactsListEl.querySelectorAll('.start-direct-chat-row[data-start-direct="true"]')
            );

            if (!rows.length) return;

            let matches = 0;

            rows.forEach((row) => {
                const source = normalizeSearch(row.getAttribute('data-search-content') || '');
                const visible = query === '' || source.includes(query);
                row.classList.toggle('d-none', !visible);
                if (visible) matches += 1;
            });

            if (contactsSearchEmpty) {
                contactsSearchEmpty.classList.toggle('d-none', matches !== 0);
            }
        };

        contactsSearchInput?.addEventListener('input', filterContacts);

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
