<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id2' => 'required|integer|exists:schools,id',
            'grade_id2' => 'required|integer|exists:grades,id',
            'agenda_id' => 'required|integer|exists:agenda,id',
            'titlear' => 'required|string|max:255',
            'bodyar' => 'required|string',
            'img_url' => 'nullable|array',
            'img_url.*' => 'file|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];
    }
}
