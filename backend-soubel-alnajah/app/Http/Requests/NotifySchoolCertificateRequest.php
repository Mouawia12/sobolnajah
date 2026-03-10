<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotifySchoolCertificateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $year = $this->input('year');
        if (is_string($year) && preg_match('/^\d{4}\s*-\s*\d{4}$/', $year)) {
            $year = str_replace('-', '/', preg_replace('/\s+/', '', $year));
        }

        $isAdmin = (bool) (
            $this->user()?->hasRole('admin')
            || $this->user()?->hasRole('administrator')
        );

        $legacyDefaults = [];
        if ($isAdmin) {
            $legacyDefaults = [
                'purpose' => $this->input('purpose', 'administrative'),
                'copies' => $this->input('copies', 1),
                'preferred_language' => $this->input('preferred_language', 'ar'),
                'delivery_method' => $this->input('delivery_method', 'printed'),
                'notes' => $this->input('notes', ''),
            ];
        }

        $this->merge([
            'id' => $this->route('id'),
            'year' => $year,
            ...$legacyDefaults,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAdmin = (bool) (
            $this->user()?->hasRole('admin')
            || $this->user()?->hasRole('administrator')
        );

        $yearRules = ['required', 'string', 'max:20'];
        if (!$isAdmin) {
            $yearRules[] = 'regex:/^\d{4}\s*\/\s*\d{4}$/';
        }

        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'year' => $yearRules,
            'purpose' => [$isAdmin ? 'nullable' : 'required', 'string', 'in:enrollment,scholarship,administrative,other'],
            'copies' => [$isAdmin ? 'nullable' : 'required', 'integer', 'min:1', 'max:5'],
            'preferred_language' => [$isAdmin ? 'nullable' : 'required', 'string', 'in:ar,fr,en'],
            'delivery_method' => [$isAdmin ? 'nullable' : 'required', 'string', 'in:printed,digital'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
