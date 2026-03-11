<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'import_token' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }
}
