<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroySectionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('Section') ?? $this->route('section'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:sections,id'],
        ];
    }
}
