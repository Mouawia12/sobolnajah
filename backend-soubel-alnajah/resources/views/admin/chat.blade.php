@extends('layoutsadmin.masteradmin')
@section('cssa')

@section('titlea')
   chat
@stop
@endsection

@section('contenta')

<div class="row">
  <div class="col-lg-12 col-12">
    <div class="row">
      <div class="col-lg-12 col-12">
        <div class="box bg-lightest d-flex flex-column" style="height:90%;">
          
          <!-- Chat messages container -->
          <div class="box-body flex-grow-1" style="overflow-y:auto;">
            <div id="chat-box" class="chat-box-one2">
              <!-- الرسائل تظهر هنا -->
            </div>
          </div>

          <!-- Chat input -->
          <div class="box-footer no-border">
            <form id="chat-form" 
                  class="d-flex justify-content-between align-items-center bg-white p-1 rounded10 b-1">
              @csrf
              <input id="msg" 
                     class="form-control b-0 me-2" 
                     type="text" 
                     placeholder="Say something..." 
                     required autocomplete="off">
              <button type="submit" class="waves-effect waves-circle btn btn-circle btn-primary">
                <i class="mdi mdi-send"></i>
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('jsa')
<script>
  const form = document.getElementById('chat-form');
  const box = document.getElementById('chat-box');
  const input = document.getElementById('msg');

  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  function escapeHtml(text) {
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function appendMessageBubble(text, sender = 'bot') {
    const isUser = sender === 'user';
    const bubble = document.createElement('div');
    bubble.className = `card d-inline-block mb-2 me-2 p-2 rounded chat-bubble ${isUser ? 'float-end bg-primary text-white' : 'float-start bg-light text-dark'}`;
    bubble.innerHTML = escapeHtml(text);
    box.appendChild(bubble);

    const clearFix = document.createElement('div');
    clearFix.className = 'clearfix';
    box.appendChild(clearFix);
    box.scrollTop = box.scrollHeight;
  }

  function splitBotReply(rawText) {
    const text = String(rawText || '').replace(/\r\n/g, '\n').trim();
    if (!text) return [];

    // Handles replies like: ### الرسالة 1 ... ### الرسالة 2 ...
    const inlineHeadingParts = text
      .split(/(?=###\s*(?:الرسالة|رسالة|message)\s*\d+\s*:?\s*)/gi)
      .map((part) => part.replace(/^###\s*/i, '').trim())
      .filter(Boolean);

    if (inlineHeadingParts.length > 1) {
      return inlineHeadingParts;
    }

    // Fallback: split paragraphs.
    const paragraphParts = text.split(/\n{2,}/).map((part) => part.trim()).filter(Boolean);
    return paragraphParts.length ? paragraphParts : [text];
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;

    appendMessageBubble(msg, 'user');
    input.value = '';

    // مؤشر الكتابة (ثلاث نقاط متحركة)
    let thinkingDiv = document.createElement('div');
    thinkingDiv.className = "text-muted my-2";
    thinkingDiv.id = "thinking";
    thinkingDiv.innerHTML = `<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span>`;
    box.appendChild(thinkingDiv);
    box.scrollTop = box.scrollHeight;

    try {
      let res = await fetch('/chat-gpt', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: msg })
      });

      let data = await res.json();

      // احذف المؤشر و أضف ردود البوت كرسائل منفصلة
      thinkingDiv.remove();
      const botParts = splitBotReply(data.bot);

      for (const part of botParts) {
        appendMessageBubble(part, 'bot');
        await sleep(180);
      }

    } catch (error) {
      console.error(error);
      thinkingDiv.remove();
      box.innerHTML += `<p class="text-danger">⚠ خطأ في الاتصال بالسيرفر</p>`;
    }
  });
</script>

<style>
/* أنيميشن ثلاث نقاط متحركة */
.typing-dots span {
  animation: blink 1.5s infinite;
  font-size: 20px;
  padding: 0 2px;
}
.typing-dots span:nth-child(2) {
  animation-delay: 0.3s;
}
.typing-dots span:nth-child(3) {
  animation-delay: 0.6s;
}
@keyframes blink {
  0% { opacity: 0.2; }
  20% { opacity: 1; }
  100% { opacity: 0.2; }
}

.chat-bubble {
  max-width: 85%;
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
@endsection
