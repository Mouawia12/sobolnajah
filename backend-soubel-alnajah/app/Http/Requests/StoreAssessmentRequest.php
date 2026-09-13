<?php

namespace App\Http\Requests;

use App\Models\Academic\Assessment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'specialization_id' => ['nullable', 'integer', 'exists:specializations,id'],
            'title' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(Assessment::TYPES)],
            'term' => ['nullable', 'integer', 'min:1', 'max:3'],
            'max_mark' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'coefficient' => ['nullable', 'numeric', 'min:0.1', 'max:20'],
            'date' => ['nullable', 'date'],
        ];
    }
}
