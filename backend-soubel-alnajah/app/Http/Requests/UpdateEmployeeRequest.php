<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'joining_date' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
