<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsenceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:studentinfos,id'],
            'hour' => ['required', 'in:hour_1,hour_2,hour_3,hour_4,hour_5,hour_6,hour_7,hour_8,hour_9'],
            'status' => ['required', 'boolean'],
        ];
    }
}
