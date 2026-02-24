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

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;

    // رسالة المستخدم
    box.innerHTML += `<div class="card d-inline-block mb-2 float-end me-2 bg-primary text-white p-2 rounded"> ${msg}</div><div class="clearfix"></div>`;
    box.scrollTop = box.scrollHeight;
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

      // احذف المؤشر و أضف رد البوت
      thinkingDiv.remove();
      box.innerHTML += `<div class="card d-inline-block mb-2 float-start me-2 bg-light p-2 rounded"> ${data.bot}</div><div class="clearfix"></div>`;
      box.scrollTop = box.scrollHeight;

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
</style>
@endsection
