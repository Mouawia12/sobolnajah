<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DestroyPromotionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'page_id' => $this->input('page_id', 2),
            'id' => $this->input('id', $this->route('Promotion') ?? $this->route('promotion')),
        ]);
    }

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
