<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotifySchoolCertificateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'year' => ['required', 'string', 'max:20', 'regex:/^\d{4}\s*\/\s*\d{4}$/'],
            'purpose' => ['required', 'string', 'in:enrollment,scholarship,administrative,other'],
            'copies' => ['required', 'integer', 'min:1', 'max:5'],
            'preferred_language' => ['required', 'string', 'in:ar,fr,en'],
            'delivery_method' => ['required', 'string', 'in:printed,digital'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
