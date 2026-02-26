<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestroyGraduatedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delete_id' => ['required', Rule::in([1, '1', 2, '2'])],
            'student_id' => ['required', 'integer', 'exists:studentinfos,id'],
            'promotion_id' => ['nullable', 'integer', 'exists:promotions,id'],
        ];
    }
}
