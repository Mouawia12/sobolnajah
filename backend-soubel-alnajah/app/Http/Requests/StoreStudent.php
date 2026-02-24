<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudent extends FormRequest
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
        $rules = [
            'section_id' => 'required|exists:sections,id',
            'prenomfr' => 'required|string',
            'prenomar' => 'required|string',
            'nomfr' => 'required|string',
            'nomar' => 'required|string',
            'email' => 'required|email',
            'gender' => 'required|in:0,1',
            'numtelephone' => 'required',
            'datenaissance' => 'required|date',
            'lieunaissance' => 'required|string',
            'wilaya' => 'required|string',
            'dayra' => 'required|string',
            'baladia' => 'required|string',
            'relationetudiant' => 'required|string',
            'adressewali' => 'required|string',
            'numtelephonewali' => 'required',
            'emailwali' => 'required|email',
            'wilayawali' => 'required|string',
            'dayrawali' => 'required|string',
            'baladiawali' => 'required|string',
            'prenomfrwali' => 'required|string',
            'prenomarwali' => 'required|string',
            'nomfrwali' => 'required|string',
            'nomarwali' => 'required|string',
        ];

        if ($this->isMethod('POST')) {
            $rules['school_id'] = 'required|exists:schools,id';
            $rules['grade_id'] = 'required|exists:schoolgrades,id';
            $rules['classroom_id'] = 'required|exists:classrooms,id';
        } else {
            $rules['school_id'] = 'sometimes|exists:schools,id';
            $rules['grade_id'] = 'sometimes|exists:schoolgrades,id';
            $rules['classroom_id'] = 'sometimes|exists:classrooms,id';
        }

        return $rules;
    }
}
