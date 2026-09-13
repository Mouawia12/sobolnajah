<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
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
            // مدرسة الموظف — إلزامية للمسؤول العام (بلا مدرسة)، وتُتجاهَل للمرتبط بمدرسة.
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            // إنشاء حساب دخول اختياري للموظف
            'create_account' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:190', 'required_if:create_account,1', 'unique:users,email'],
        ];
    }
}
