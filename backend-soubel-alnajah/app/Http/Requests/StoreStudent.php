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
            'password' => 'nullable|string|min:8',
        ];

        if ($this->isMethod('POST')) {
            $rules['school_id'] = 'required|exists:schools,id';
            $rules['grade_id'] = 'required|exists:schoolgrades,id';
            $rules['classroom_id'] = 'required|exists:classrooms,id';
            $rules['relationetudiant'] = 'required|string';
            $rules['adressewali'] = 'required|string';
            $rules['numtelephonewali'] = 'required';
            $rules['emailwali'] = 'required|email';
            $rules['wilayawali'] = 'required|string';
            $rules['dayrawali'] = 'required|string';
            $rules['baladiawali'] = 'required|string';
            $rules['prenomfrwali'] = 'required|string';
            $rules['prenomarwali'] = 'required|string';
            $rules['nomfrwali'] = 'required|string';
            $rules['nomarwali'] = 'required|string';
        } else {
            $rules['school_id'] = 'sometimes|exists:schools,id';
            $rules['grade_id'] = 'sometimes|exists:schoolgrades,id';
            $rules['classroom_id'] = 'sometimes|exists:classrooms,id';
            $rules['relationetudiant'] = 'sometimes|nullable|string';
            $rules['adressewali'] = 'sometimes|nullable|string';
            $rules['numtelephonewali'] = 'sometimes|nullable';
            $rules['emailwali'] = 'sometimes|nullable|email';
            $rules['wilayawali'] = 'sometimes|nullable|string';
            $rules['dayrawali'] = 'sometimes|nullable|string';
            $rules['baladiawali'] = 'sometimes|nullable|string';
            $rules['prenomfrwali'] = 'sometimes|nullable|string';
            $rules['prenomarwali'] = 'sometimes|nullable|string';
            $rules['nomfrwali'] = 'sometimes|nullable|string';
            $rules['nomarwali'] = 'sometimes|nullable|string';
        }

        return $rules;
    }
}
