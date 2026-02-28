<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_cover_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
            'published_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date', 'after:published_at'],
        ];
    }
}
