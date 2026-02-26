<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimetableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'title' => ['nullable', 'string', 'max:160'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_published' => ['nullable', 'boolean'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'entries.*.period_index' => ['required', 'integer', 'between:1,12'],
            'entries.*.starts_at' => ['nullable', 'date_format:H:i'],
            'entries.*.ends_at' => ['nullable', 'date_format:H:i'],
            'entries.*.subject_name' => ['required', 'string', 'max:140'],
            'entries.*.teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'entries.*.room_name' => ['nullable', 'string', 'max:80'],
            'entries.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
