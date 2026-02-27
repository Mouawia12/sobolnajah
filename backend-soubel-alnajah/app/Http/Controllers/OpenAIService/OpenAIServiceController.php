<?php

namespace App\Http\Controllers\OpenAIService;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOpenAiMessageRequest;
use Illuminate\Support\Facades\Http;

class OpenAIServiceController extends Controller
{
    public function index()
    {
        return view('admin.chat', [
            'notify' => $this->notifications(),
        ]);
    }

    public function send(SendOpenAiMessageRequest $request)
    {
        $validated = $request->validated();

        try {
            // إرسال الطلب مباشرة لـ OpenAI API
            $response = Http::timeout(60) // مهلة 60 ثانية
                ->withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $validated['message']],
                    ],
                ]);

            // إذا كان الرد فيه خطأ من OpenAI
            if ($response->failed()) {
                return response()->json([
                    'user' => $validated['message'],
                    'bot'  => '⚠ خطأ من API: ' . ($response->json()['error']['message'] ?? $response->status())
                ], $response->status());
            }

            // إذا كان الرد ناجح
            $reply = $response->json()['choices'][0]['message']['content'] ?? null;

            if (!$reply) {
                return response()->json([
                    'user' => $validated['message'],
                    'bot'  => '⚠ لم يتم العثور على رد من API'
                ], 500);
            }

            return response()->json([
                'user' => $validated['message'],
                'bot'  => $reply,
            ]);

        } catch (\Exception $e) {
            // أي خطأ غير متوقع (انقطاع نت، timeout ...)
            return response()->json([
                'user' => $validated['message'],
                'bot'  => '⚠ استثناء: ' . $e->getMessage()
            ], 500);
        }
    }
}
