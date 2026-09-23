<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacher extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $specialization_id = $this->specialization_id;
        $gender = $this->gender;
        $name_teacherfr = $this->name_teacherfr;
        $name_teacherar = $this->name_teacherar;
        $teacherId = $this->route('Teacher') ?? $this->route('teacher');

        $passwordRules = ['nullable', 'string', 'min:8', 'confirmed'];

        return [
            'name_teacherfr' => 'required',
            'name_teacherar' => 'required',
            // بيانات المعلّم اختيارية (يمكن إكمالها لاحقاً).
            'address' => 'nullable|string|max:500',
            'email' => 'required|email',
            'gender' => 'nullable|in:0,1',
            'joining_date' => 'nullable|date',
            'password' => $passwordRules,

            'specialization_id' => [
                'nullable',
                'integer',
                'exists:specializations,id',
                Rule::unique('teachers')
                    ->ignore($teacherId)
                    ->where('specialization_id', $specialization_id)
                    ->where('gender', $gender)
                    ->where(function ($query) use ($name_teacherfr, $name_teacherar) {
                        return $query->where('name->fr', $name_teacherfr)
                            ->orWhere('name->ar', $name_teacherar);
                    }),
            ],

        ];

    }
}
