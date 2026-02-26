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
            'school_id2' => 'required|integer|exists:schools,id',
            'grade_id2' => 'required|integer|exists:grades,id',
            'agenda_id' => 'required|integer|exists:agenda,id',
            'titlear' => 'required|string|max:255',
            'bodyar' => 'required|string',
            'img_url' => 'required|array|min:1',
            'img_url.*' => 'file|image|mimes:jpeg,png,jpg,webp|max:5120',
           
        ];
    }
}
