<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublication extends FormRequest
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
            'school_id2' => 'required',
            'grade_id2' => 'required',
            'agenda_id' => 'required',
            'titlear' => 'required',
            'bodyar' => 'required',
            'img_url' => 'required',
           
        ];
    }
}
