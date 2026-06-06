<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFamilyPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receipt_number' => ['required', 'string', 'max:70'],
            'paid_on' => ['required', 'date'],
            'payment_method' => ['nullable', Rule::in(['cash', 'transfer', 'card', 'other'])],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.contract_id' => ['required', 'integer', 'distinct', 'exists:student_contracts,id'],
            'items.*.amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => trans('accounting.family_payment.no_selection'),
            'items.min' => trans('accounting.family_payment.no_selection'),
        ];
    }
}
