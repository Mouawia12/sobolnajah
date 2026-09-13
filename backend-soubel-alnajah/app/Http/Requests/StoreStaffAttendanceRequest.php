<?php

namespace App\Http\Requests;

use App\Models\HR\StaffAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_type' => ['required', Rule::in([StaffAttendance::TYPE_TEACHER, StaffAttendance::TYPE_EMPLOYEE])],
            'staff_id' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'integer', Rule::in(StaffAttendance::STATUSES)],
            'date' => ['nullable', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
