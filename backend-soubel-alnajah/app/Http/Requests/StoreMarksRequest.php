<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mark' => ['nullable', 'array'],
            'mark.*' => ['nullable', 'numeric', 'min:0'],
            'absent' => ['nullable', 'array'],
            'absent.*' => ['nullable', 'in:0,1'],
        ];
    }
}
