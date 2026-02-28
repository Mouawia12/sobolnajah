<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePortalUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $role = (string) $this->input('role');
        $currentSchoolId = $this->user()?->school_id;

        return [
            'role' => ['required', Rule::in(['admin', 'teacher', 'student', 'guardian', 'accountant'])],
            'first_name_fr' => ['required_without:first_name_ar', 'nullable', 'string', 'max:120'],
            'last_name_fr' => ['required_without:last_name_ar', 'nullable', 'string', 'max:120'],
            'first_name_ar' => ['required_without:first_name_fr', 'nullable', 'string', 'max:120'],
            'last_name_ar' => ['required_without:last_name_fr', 'nullable', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'school_id' => [
                Rule::requiredIf(!$currentSchoolId && $role !== 'admin'),
                'nullable',
                'integer',
                'exists:schools,id',
            ],
            'specialization_id' => [Rule::requiredIf($role === 'teacher'), 'nullable', 'integer', 'exists:specializations,id'],
            'gender' => [Rule::requiredIf(in_array($role, ['teacher', 'student'], true)), 'nullable', Rule::in(['0', '1', 0, 1])],
            'joining_date' => [Rule::requiredIf($role === 'teacher'), 'nullable', 'date'],
            'address' => [Rule::requiredIf(in_array($role, ['teacher', 'guardian'], true)), 'nullable', 'string', 'max:500'],
            'guardian_relation' => [Rule::requiredIf($role === 'guardian'), 'nullable', 'string', 'max:190'],
            'guardian_wilaya' => [Rule::requiredIf($role === 'guardian'), 'nullable', 'string', 'max:190'],
            'guardian_dayra' => [Rule::requiredIf($role === 'guardian'), 'nullable', 'string', 'max:190'],
            'guardian_baladia' => [Rule::requiredIf($role === 'guardian'), 'nullable', 'string', 'max:190'],
            'guardian_phone' => [Rule::requiredIf($role === 'guardian'), 'nullable', 'string', 'max:40'],
            'section_id' => [Rule::requiredIf($role === 'student'), 'nullable', 'integer', 'exists:sections,id'],
            'student_birth_date' => [Rule::requiredIf($role === 'student'), 'nullable', 'date'],
            'student_birth_place' => [Rule::requiredIf($role === 'student'), 'nullable', 'string', 'max:190'],
            'student_wilaya' => [Rule::requiredIf($role === 'student'), 'nullable', 'string', 'max:190'],
            'student_dayra' => [Rule::requiredIf($role === 'student'), 'nullable', 'string', 'max:190'],
            'student_baladia' => [Rule::requiredIf($role === 'student'), 'nullable', 'string', 'max:190'],
            'student_phone' => [Rule::requiredIf($role === 'student'), 'nullable', 'string', 'max:40'],
            'guardian_user_id' => [Rule::requiredIf($role === 'student'), 'nullable', 'integer', 'exists:users,id'],
        ];
    }
}
