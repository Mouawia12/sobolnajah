<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroySchoolgradeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('Schoolgrade') ?? $this->route('schoolgrade'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:schoolgrades,id'],
        ];
    }
}
