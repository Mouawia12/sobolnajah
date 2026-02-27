<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateChatGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'members' => ['required', 'array', 'min:2'],
            'members.*' => [
                'distinct',
                Rule::exists('users', 'id')->whereNot('id', $this->user()?->id),
            ],
        ];
    }
}
