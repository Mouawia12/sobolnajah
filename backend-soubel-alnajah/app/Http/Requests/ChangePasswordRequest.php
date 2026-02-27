<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8', 'different:password'],
            'confirmNewPassword' => ['required', 'same:newPassword'],
        ];
    }
}
