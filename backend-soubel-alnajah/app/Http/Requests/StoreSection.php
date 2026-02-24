<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreSection extends FormRequest
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
    $school_id = $this->school_id;
    $grade_id = $this->grade_id;
    $classroom_id = $this->classroom_id;
    $name_sectionfr = $this->name_sectionfr;   
    $name_sectionar = $this->name_sectionar;

    // إذا كان الطلب من تحديث (PUT/PATCH) اجلب ID القسم الحالي
    $sectionId = $this->route('Section') ?? $this->route('section'); 

    $uniqueRule = Rule::unique('sections')
        ->where('school_id', $school_id)
        ->where('grade_id', $grade_id)
        ->where('classroom_id', $classroom_id)
        ->where(function ($query) use ($name_sectionfr, $name_sectionar) {
            return $query->where('name_section->fr', $name_sectionfr)
                         ->orWhere('name_section->ar', $name_sectionar);
        });

    // لو تحديث استثني الـ ID الحالي من التحقق
    if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
        $uniqueRule->ignore($sectionId);
    }

    return [
        'name_sectionfr' => 'required',
        'name_sectionar' => 'required',
        'grade_id' => 'required',
        'classroom_id' => 'required',
        'school_id' => ['required', $uniqueRule],
    ];
}


    public function messages()
    {
        return [
            'school_id.unique' => trans('validation.uniqueSchool'),
        ];
    }

}
