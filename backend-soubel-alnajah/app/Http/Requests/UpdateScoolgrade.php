<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScoolgrade extends FormRequest
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
       $name_gradefr=$this->name_gradefr;
       $name_gradear=$this->name_gradear;

        return [
             'name_gradefr' => 'required',
             'name_gradear' => 'required',
             'notesfr' => 'required',
             'notesar' => 'required',
    
             'school_id' => [
                'required',
                Rule::unique('schoolgrades')->where(function ($query) use($school_id,$name_gradefr,$name_gradear) {
                    return $query->where('school_id', $school_id)
                                 ->where('name_grade->fr', $name_gradefr)
                                 ->where('name_grade->ar', $name_gradear);
                    
                }),
            ],

        ];
    }
}
