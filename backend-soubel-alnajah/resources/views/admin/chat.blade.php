@extends('layoutsadmin.masteradmin')

@section('titlea')
  chat
@stop

@section('contenta')
<div class="row">
  <div class="col-12">
    <div class="ai-chat-shell card">
      <div class="ai-chat-header">
        <div class="ai-chat-brand">
          <div class="ai-chat-avatar">AI</div>
          <div class="ai-chat-headings">
            <h4 id="chat-title" class="mb-0"></h4>
            <small id="chat-subtitle"></small>
          </div>
        </div>
        <button id="clear-chat" type="button" class="btn btn-outline-secondary btn-sm"></button>
      </div>

      <div id="chat-scroll" class="ai-chat-body">
        <div id="chat-box" class="ai-chat-box"></div>
      </div>

      <div class="ai-chat-footer">
        <form id="chat-form" class="ai-chat-form">
          @csrf
          <textarea
            id="msg"
            rows="1"
            class="form-control ai-chat-input"
            required
            autocomplete="off"
          ></textarea>
          <button id="send-btn" type="submit" class="btn btn-primary ai-send-btn" aria-label="send">
            <i class="mdi mdi-send-up"></i>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@section('jsa')
<script>
  const form = document.getElementById('chat-form');
  const box = document.getElementById('chat-box');
  const scrollContainer = document.getElementById('chat-scroll');
  const input = document.getElementById('msg');
  const sendBtn = document.getElementById('send-btn');
  const clearBtn = document.getElementById('clear-chat');
  const chatTitle = document.getElementById('chat-title');
  const chatSubtitle = document.getElementById('chat-subtitle');

  const isArabic = (document.documentElement.lang || '').toLowerCase().startsWith('ar') || document.body.dir === 'rtl';

  const i18n = {
    ar: {
      title: 'مساعد الذكاء الاصطناعي',
      subtitle: 'متصل الآن - ردود سريعة ومنظمة',
      placeholder: 'اكتب رسالتك هنا...',
      clear: 'مسح المحادثة',
      welcome: 'مرحبا بك! كيف يمكنني مساعدتك اليوم؟',
      thinking: 'جاري الكتابة...',
      error: 'حدث خطأ في الاتصال بالخادم.',
      emptyMessage: 'اكتب رسالة اولا.'
    },
    en: {
      title: 'AI Assistant',
      subtitle: 'Online - Fast and structured replies',
      placeholder: 'Type your message...',
      clear: 'Clear chat',
      welcome: 'Welcome! How can I help you today?',
      thinking: 'Typing...',
      error: 'Server connection error.',
      emptyMessage: 'Please type a message first.'
    }
  };

  const t = (key) => (isArabic ? i18n.ar[key] : i18n.en[key]);
  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function currentTime() {
    const now = new Date();
    return now.toLocaleTimeString(isArabic ? 'ar-DZ' : 'en-US', {
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function splitBotReply(rawText) {
    const text = String(rawText || '').replace(/\r\n/g, '\n').trim();
    if (!text) return [];

    const numberedParts = text
      .split(/(?=###\s*(?:الرسالة|رسالة|message)\s*\d+\s*:?\s*)/gi)
      .map((part) => part.replace(/^###\s*/i, '').trim())
      .filter(Boolean);

    if (numberedParts.length > 1) return numberedParts;

    const paragraphs = text
      .split(/\n{2,}/)
      .map((part) => part.trim())
      .filter(Boolean);

    return paragraphs.length ? paragraphs : [text];
  }

  function createBubble({ text, sender, muted = false }) {
    const wrapper = document.createElement('div');
    wrapper.className = `msg-row ${sender === 'user' ? 'msg-user' : 'msg-bot'}`;

    const bubble = document.createElement('div');
    bubble.className = `msg-bubble ${sender === 'user' ? 'user-bubble' : 'bot-bubble'} ${muted ? 'muted-bubble' : ''}`;

    const content = document.createElement('div');
    content.className = 'msg-content';
    content.innerHTML = escapeHtml(text);

    const meta = document.createElement('div');
    meta.className = 'msg-meta';
    meta.textContent = currentTime();

    bubble.appendChild(content);
    bubble.appendChild(meta);
    wrapper.appendChild(bubble);
    box.appendChild(wrapper);

    scrollContainer.scrollTop = scrollContainer.scrollHeight;
    return wrapper;
  }

  function addWelcome() {
    if (box.children.length > 0) return;
    createBubble({ text: t('welcome'), sender: 'bot', muted: true });
  }

  async function renderBotResponse(raw) {
    const parts = splitBotReply(raw);
    for (const part of parts) {
      createBubble({ text: part, sender: 'bot' });
      await sleep(130);
    }
  }

  function setUiLanguage() {
    chatTitle.textContent = t('title');
    chatSubtitle.textContent = t('subtitle');
    input.placeholder = t('placeholder');
    clearBtn.textContent = t('clear');
  }

  function autoResizeTextarea() {
    input.style.height = 'auto';
    input.style.height = `${Math.min(input.scrollHeight, 180)}px`;
  }

  setUiLanguage();
  addWelcome();

  input.addEventListener('input', autoResizeTextarea);
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  clearBtn.addEventListener('click', () => {
    box.innerHTML = '';
    addWelcome();
    input.focus();
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const msg = input.value.trim();
    if (!msg) {
      createBubble({ text: t('emptyMessage'), sender: 'bot', muted: true });
      return;
    }

    createBubble({ text: msg, sender: 'user' });
    input.value = '';
    autoResizeTextarea();

    const thinkingNode = createBubble({ text: t('thinking'), sender: 'bot', muted: true });

    sendBtn.disabled = true;
    input.disabled = true;

    try {
      const res = await fetch('/chat-gpt', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: msg })
      });

      const data = await res.json();
      thinkingNode.remove();

      if (!res.ok) {
        createBubble({ text: data.bot || t('error'), sender: 'bot' });
      } else {
        await renderBotResponse(data.bot || '...');
      }
    } catch (error) {
      console.error(error);
      thinkingNode.remove();
      createBubble({ text: t('error'), sender: 'bot' });
    } finally {
      sendBtn.disabled = false;
      input.disabled = false;
      input.focus();
    }
  });
</script>

<style>
  .ai-chat-shell {
    --ai-surface: #ffffff;
    --ai-surface-soft: #f3f7ff;
    --ai-border: #dbe4f2;
    --ai-text: #0f172a;
    --ai-muted: #64748b;
    --ai-bot-bg: #ffffff;
    --ai-bot-border: #e5eaf3;
    --ai-user-bg: linear-gradient(140deg, #0d6efd 0%, #0b5ed7 100%);
  }

  body.dark-skin .ai-chat-shell {
    --ai-surface: #0f2238;
    --ai-surface-soft: #122d47;
    --ai-border: rgba(148, 163, 184, 0.25);
    --ai-text: #dbeafe;
    --ai-muted: #9ab7d8;
    --ai-bot-bg: #17344f;
    --ai-bot-border: rgba(148, 163, 184, 0.26);
    --ai-user-bg: linear-gradient(140deg, #3b82f6 0%, #2563eb 100%);
  }

  .ai-chat-shell {
    border: 0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(16, 24, 40, 0.08);
    min-height: 76vh;
    background: var(--ai-surface-soft);
    border: 1px solid var(--ai-border);
  }

  .ai-chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    border-bottom: 1px solid var(--ai-border);
    background: var(--ai-surface);
  }

  .ai-chat-brand {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .ai-chat-avatar {
    width: 38px;
    height: 38px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.5px;
  }

  .ai-chat-headings h4 {
    font-size: 15px;
    font-weight: 700;
    color: var(--ai-text);
    line-height: 1.2;
  }

  .ai-chat-headings small {
    color: var(--ai-muted);
    font-size: 12px;
  }

  .ai-chat-body {
    flex: 1;
    height: calc(76vh - 130px);
    overflow-y: auto;
    padding: 16px;
  }

  .ai-chat-box {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .msg-row {
    display: flex;
    width: 100%;
  }

  .msg-user {
    justify-content: flex-end;
  }

  .msg-bot {
    justify-content: flex-start;
  }

  .msg-bubble {
    max-width: min(78%, 820px);
    border-radius: 14px;
    padding: 10px 12px 8px;
    box-shadow: 0 3px 14px rgba(15, 23, 42, 0.06);
    animation: bubbleIn 0.18s ease-out;
  }

  .user-bubble {
    background: var(--ai-user-bg);
    color: #fff;
    border-bottom-right-radius: 5px;
  }

  .bot-bubble {
    background: var(--ai-bot-bg);
    color: var(--ai-text);
    border: 1px solid var(--ai-bot-border);
    border-bottom-left-radius: 5px;
  }

  .muted-bubble {
    opacity: 0.9;
  }

  .msg-content {
    white-space: pre-wrap;
    word-break: break-word;
    line-height: 1.65;
    font-size: 14px;
  }

  .msg-meta {
    margin-top: 6px;
    font-size: 11px;
    opacity: 0.7;
  }

  .ai-chat-footer {
    border-top: 1px solid var(--ai-border);
    background: var(--ai-surface);
    padding: 10px 12px 12px;
  }

  .ai-chat-form {
    display: flex;
    align-items: flex-end;
    gap: 10px;
  }

  .ai-chat-input {
    border: 1px solid var(--ai-border);
    border-radius: 12px;
    resize: none;
    min-height: 44px;
    max-height: 180px;
    line-height: 1.5;
    padding: 10px 12px;
    background: var(--ai-surface-soft);
    color: var(--ai-text);
  }

  .ai-chat-input::placeholder {
    color: var(--ai-muted);
  }

  .ai-chat-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.12);
    background: var(--ai-surface-soft);
    color: var(--ai-text);
  }

  .ai-send-btn {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    flex-shrink: 0;
    box-shadow: 0 10px 20px rgba(13, 110, 253, 0.2);
  }

  .ai-send-btn[disabled] {
    opacity: 0.75;
  }

  body.dark-skin .ai-chat-shell .btn-outline-secondary {
    color: #bcd6f3;
    border-color: rgba(148, 163, 184, 0.35);
    background: rgba(15, 35, 58, 0.6);
  }

  body.dark-skin .ai-chat-shell .btn-outline-secondary:hover {
    color: #ffffff;
    border-color: rgba(147, 197, 253, 0.65);
    background: rgba(37, 99, 235, 0.25);
  }

  @keyframes bubbleIn {
    from {
      opacity: 0;
      transform: translateY(8px) scale(0.98);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  @media (max-width: 768px) {
    .ai-chat-shell {
      min-height: 70vh;
      border-radius: 12px;
    }

    .ai-chat-body {
      height: calc(70vh - 130px);
      padding: 12px;
    }

    .msg-bubble {
      max-width: 90%;
    }
  }
</style>
@endsection
