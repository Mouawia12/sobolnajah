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

        $specialization_id=$this->specialization_id;
        $gender=$this->gender;
        $name_teacherfr=$this->name_teacherfr;   
        $name_teacherar=$this->name_teacherar;
 
 
         return [
            'name_teacherfr' => 'required',
            'name_teacherar' => 'required',
            'address' => 'required',
            'email' => 'required|email',
            'gender' => 'required',
     
             'specialization_id' => [
             'required',
                 Rule::unique('teachers')->where('specialization_id', $specialization_id)  
                                           ->where('gender', $gender) 
                                           ->where(function ($query) use($name_teacherfr,$name_teacherar) {
                                           return $query->where('name->fr', $name_teacherfr)
                                                         ->orwhere('name->ar', $name_teacherar)
                                  ;
                 }),
             ],
 
         ];

    }
}
