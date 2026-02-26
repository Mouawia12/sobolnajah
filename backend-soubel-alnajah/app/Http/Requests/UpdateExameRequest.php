<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'specialization_id' => ['required', 'integer', 'exists:specializations,id'],
            'grade_id' => ['required', 'integer', 'exists:schoolgrades,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'Annscolaire' => ['required', 'integer', 'min:2000', 'max:2100'],
            'file_url' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
        ];
    }
}
