<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroySchoolRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('School') ?? $this->route('school'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:schools,id'],
        ];
    }
}
