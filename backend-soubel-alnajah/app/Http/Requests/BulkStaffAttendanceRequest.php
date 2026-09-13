<?php

namespace App\Http\Requests;

use App\Models\HR\StaffAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'integer', Rule::in(StaffAttendance::STATUSES)],
            'date' => ['nullable', 'date'],
        ];
    }
}
