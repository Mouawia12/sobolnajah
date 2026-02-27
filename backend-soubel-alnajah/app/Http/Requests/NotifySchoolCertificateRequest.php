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
            'year' => ['required', 'string', 'max:20'],
            'namefr' => ['required', 'string', 'max:255'],
            'namear' => ['required', 'string', 'max:255'],
        ];
    }
}
