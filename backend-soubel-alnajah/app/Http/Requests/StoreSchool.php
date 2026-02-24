<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchool extends FormRequest
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
        return [
        
            'name_schoolfr' => 'required|unique:schools,name_school->fr,'.$this->id,
            'name_schoolar' => 'required|unique:schools,name_school->ar,'.$this->id,
        
        ];
    }
}
