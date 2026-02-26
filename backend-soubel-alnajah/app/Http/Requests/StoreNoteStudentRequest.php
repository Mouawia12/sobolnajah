<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:studentinfos,id'],
            'Anneescolaire' => ['required', 'in:1,2,3'],
            'note_file' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
