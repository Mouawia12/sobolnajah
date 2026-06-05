<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ImportStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ملفات الوزارة (Eleve.xls) هي HTML بامتداد xls، لذا نفحص الامتداد والمحتوى بدل MIME.
            'file' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (!$value instanceof UploadedFile) {
                        $fail('الملف المرفوع غير صالح.');
                        return;
                    }

                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['xls', 'xlsx', 'html', 'htm'], true)) {
                        $fail('امتداد الملف غير مدعوم. المسموح: xls, xlsx, html.');
                        return;
                    }

                    $head = (string) @file_get_contents($value->getRealPath(), false, null, 0, 4096);
                    if (stripos($head, '<table') === false && stripos($head, '<html') === false) {
                        $fail('الملف ليس ملف تلاميذ صادراً عن منظومة الوزارة (Eleve.xls).');
                    }
                },
            ],
            'import_token' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }
}
