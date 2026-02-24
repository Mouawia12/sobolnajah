<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class OpenAiTranslationService
{
    /**
     * Translate the given Arabic text into French and English using OpenAI.
     *
     * @return array{fr:string,en:string}
     */
    public function translateToFrenchAndEnglish(string $arabicText): array
    {
        if (trim($arabicText) === '') {
            return ['fr' => '', 'en' => ''];
        }

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional translator. Translate the given Arabic text into French and English. Return strictly in JSON: {"fr": "...", "en": "..."}',
                ],
                [
                    'role' => 'user',
                    'content' => $arabicText,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);

        $payload = $response['choices'][0]['message']['content'] ?? '{}';
        $translations = json_decode($payload, true);

        if (!is_array($translations)) {
            return ['fr' => '', 'en' => ''];
        }

        return [
            'fr' => (string) ($translations['fr'] ?? ''),
            'en' => (string) ($translations['en'] ?? ''),
        ];
    }

    /**
     * Translate publication title and body into three languages.
     *
     * @return array{title:array{ar:string,fr:string,en:string},body:array{ar:string,fr:string,en:string}}
     */
    public function translatePublicationContent(string $title, string $body): array
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a translator. Detect the input language automatically and always provide translations into Arabic, French, and English. Never return the original text unless it is already in that target language. Return JSON with keys "titleAr", "bodyAr", "titleFr", "bodyFr", "titleEn", "bodyEn".',
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'title' => $title,
                        'body' => $body,
                    ]),
                ],
            ],
        ]);

        $content = $response['choices'][0]['message']['content'] ?? '{}';
        $translations = json_decode($content, true);

        $titleTranslations = [
            'ar' => (string) ($translations['titleAr'] ?? $title),
            'fr' => (string) ($translations['titleFr'] ?? $title),
            'en' => (string) ($translations['titleEn'] ?? $title),
        ];

        $bodyTranslations = [
            'ar' => (string) ($translations['bodyAr'] ?? $body),
            'fr' => (string) ($translations['bodyFr'] ?? $body),
            'en' => (string) ($translations['bodyEn'] ?? $body),
        ];

        return [
            'title' => $titleTranslations,
            'body' => $bodyTranslations,
        ];
    }
}
