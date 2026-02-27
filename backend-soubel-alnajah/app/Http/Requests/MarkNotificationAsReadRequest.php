<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkNotificationAsReadRequest extends FormRequest
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
            'id' => ['required', 'uuid', 'exists:notifications,id'],
        ];
    }
}
