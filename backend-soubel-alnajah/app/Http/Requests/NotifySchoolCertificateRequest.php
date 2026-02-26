<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotifySchoolCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'string', 'max:20'],
            'namefr' => ['required', 'string', 'max:255'],
            'namear' => ['required', 'string', 'max:255'],
        ];
    }
}
