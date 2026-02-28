<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'title' => ['nullable', 'string', 'max:160'],
            'branch_name' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'visibility' => ['required', Rule::in(['public', 'authenticated'])],
            'approved_at' => ['nullable', 'date'],
            'signature_text' => ['nullable', 'string', 'max:160'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.slot_index' => ['required', 'integer', 'between:1,12'],
            'slots.*.label' => ['nullable', 'string', 'max:40'],
            'slots.*.starts_at' => ['nullable', 'date_format:H:i'],
            'slots.*.ends_at' => ['nullable', 'date_format:H:i'],
            'entries' => ['nullable', 'array'],
            'entries.*.*.subject_name' => ['nullable', 'string', 'max:140'],
            'entries.*.*.class_name' => ['nullable', 'string', 'max:100'],
            'entries.*.*.room_name' => ['nullable', 'string', 'max:80'],
            'entries.*.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
