<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestroyPromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_id' => ['required', Rule::in([1, '1', 2, '2'])],
            'id' => ['required_if:page_id,2', 'integer', 'exists:promotions,id'],
        ];
    }
}
