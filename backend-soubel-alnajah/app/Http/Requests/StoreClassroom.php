<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class StoreClassroom extends FormRequest
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
       $school_id=$this->school_id;
       $grade_id=$this->grade_id;
       $name_classfr=$this->name_classfr;   
       $name_classar=$this->name_classar;


        return [
            'name_classfr' => 'required',
            'name_classar' => 'required',
            'grade_id' => 'required',
            'school_id' => [
                'required',
                Rule::unique('classrooms')->where('school_id', $school_id)  
                                          ->where('grade_id', $grade_id) 
                                          ->where(function ($query) use($name_classfr,$name_classar) {
                                          return $query->where('name_class->fr', $name_classfr)
                                                        ->orwhere('name_class->ar', $name_classar)
                                 ;
                }),
            ],

        ];

    }



    public function messages()
    {
        return [
            'school_id.unique' => trans('validation.unique'),
        ];
    }
    
}
