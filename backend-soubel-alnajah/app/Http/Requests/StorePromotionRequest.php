<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'grade_id' => ['required', 'integer', 'exists:schoolgrades,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
            'school_id_new' => ['required', 'integer', 'exists:schools,id'],
            'grade_id_new' => ['required', 'integer', 'exists:schoolgrades,id'],
            'classroom_id_new' => ['required', 'integer', 'exists:classrooms,id'],
            'section_id_new' => ['required', 'integer', 'exists:sections,id', 'different:section_id'],
        ];
    }
}
