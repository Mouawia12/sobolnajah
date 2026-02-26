<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year' => ['required', 'string', 'max:20'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'plan_type' => ['required', Rule::in(['yearly', 'monthly', 'installments'])],
            'installments_count' => ['nullable', 'integer', 'min:1', 'max:24'],
            'payment_plan_id' => ['nullable', 'integer', 'exists:payment_plans,id'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'status' => ['required', Rule::in(['draft', 'active', 'partial', 'paid', 'overdue'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
