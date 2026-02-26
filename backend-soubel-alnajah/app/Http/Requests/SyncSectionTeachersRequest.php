<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncSectionTeachersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['nullable', 'array'],
            'teacher_id.*' => ['integer', 'exists:teachers,id'],
        ];
    }
}
