<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_id' => ['required', 'integer', 'exists:student_contracts,id'],
            'installment_id' => ['nullable', 'integer', 'exists:contract_installments,id'],
            'receipt_number' => ['required', 'string', 'max:80'],
            'paid_on' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['nullable', Rule::in(['cash', 'transfer', 'card', 'other'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
