<style>
    .chat-shell {
        --chat-surface: #ffffff;
        --chat-surface-alt: #f8fafc;
        --chat-border: #e2e8f0;
        --chat-text: #0f172a;
        --chat-muted: #64748b;
        --chat-soft-accent: rgba(79, 70, 229, 0.08);
        --chat-bubble-other: #ffffff;
        --chat-bubble-other-border: #e2e8f0;
        --chat-bubble-me: linear-gradient(135deg, #2563eb, #1d4ed8);
    }

    body.dark-skin .chat-shell {
        --chat-surface: #0f2238;
        --chat-surface-alt: #102c47;
        --chat-border: rgba(148, 163, 184, 0.22);
        --chat-text: #dbeafe;
        --chat-muted: #93b4d6;
        --chat-soft-accent: rgba(96, 165, 250, 0.16);
        --chat-bubble-other: #17344f;
        --chat-bubble-other-border: rgba(148, 163, 184, 0.26);
        --chat-bubble-me: linear-gradient(135deg, #3b82f6, #2563eb);
    }

    .chat-shell {
        min-height: 70vh;
        background: var(--chat-surface);
        border: 1px solid var(--chat-border);
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
        background: var(--chat-soft-accent);
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
        background: var(--chat-surface-alt);
        border-top: 1px solid var(--chat-border);
        border-bottom: 1px solid var(--chat-border);
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
        background: var(--chat-bubble-me);
        color: #fff;
    }

    .chat-message-card.them {
        background: var(--chat-bubble-other);
        border: 1px solid var(--chat-bubble-other-border);
        color: var(--chat-text);
    }

    .message-time {
        font-size: 12px;
        margin-top: 4px;
        display: inline-block;
        color: var(--chat-muted);
    }
    .message-time.text-white-50,
    .message-time.text-white {
        color: #cbd5f5 !important;
    }

    .chat-empty-state {
        text-align: center;
        color: var(--chat-muted);
        padding: 40px 10px;
    }

    .chat-empty-state i {
        font-size: 42px;
        display: block;
        margin-bottom: 12px;
    }

    .chat-input-area {
        background: var(--chat-surface);
        border-top: 1px solid var(--chat-border);
        padding: 1rem;
    }

    .chat-typing {
        font-size: 13px;
        color: var(--chat-muted);
    }

    .media-list-hover .media {
        padding: 0.65rem 0.35rem;
    }

    .media-list-hover .media:not(:last-child) {
        border-bottom: 1px solid var(--chat-border);
    }

    .start-direct-chat-row {
        cursor: pointer;
        transition: background-color 0.25s ease;
        border-radius: 12px;
        padding: 0.75rem 0.35rem;
    }

    .start-direct-chat-row:hover,
    .start-direct-chat-row:focus {
        background: var(--chat-soft-accent);
    }

    .btn-chat-send.btn-chat-disabled {
        pointer-events: none;
        opacity: 0.5;
    }

    .send-icon {
        width: 18px;
        height: 18px;
        display: inline-block;
        vertical-align: middle;
    }

    .chat-shell .box-header,
    .chat-shell .box-body {
        background: var(--chat-surface);
        color: var(--chat-text);
    }

    .chat-shell .text-muted,
    .chat-shell .room-preview {
        color: var(--chat-muted) !important;
    }

    .chat-shell .form-control {
        background: var(--chat-surface-alt);
        border-color: var(--chat-border);
        color: var(--chat-text);
    }

    .chat-shell .form-control::placeholder {
        color: var(--chat-muted);
    }

    .chat-shell .form-control:focus {
        background: var(--chat-surface-alt);
        border-color: #3b82f6;
        color: var(--chat-text);
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.18);
    }

    body.dark-skin .chat-shell .nav-tabs .nav-link {
        color: #9ec3ec;
        border-color: transparent;
    }

    body.dark-skin .chat-shell .nav-tabs .nav-link.active {
        color: #ffffff;
        background: rgba(37, 99, 235, 0.42);
        border-color: rgba(147, 197, 253, 0.3);
    }
</style>
