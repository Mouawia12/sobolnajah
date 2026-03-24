<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $slots = collect($this->input('slots', []))
            ->map(function ($slot) {
                if (!is_array($slot)) {
                    return $slot;
                }

                $slot['starts_at'] = $this->normalizeTimeValue($slot['starts_at'] ?? null);
                $slot['ends_at'] = $this->normalizeTimeValue($slot['ends_at'] ?? null);

                return $slot;
            })
            ->all();

        $this->merge([
            'slots' => $slots,
        ]);
    }

    public function rules(): array
    {
        return [
            'school_id' => [
                Rule::requiredIf(fn () => !$this->user()?->school_id),
                'nullable',
                'integer',
                'exists:schools,id',
            ],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'academic_year' => ['required', 'string', 'max:20'],
            'title' => ['nullable', 'string', 'max:160'],
            'branch_name' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'visibility' => ['required', Rule::in(['public', 'authenticated'])],
            'approved_at' => ['nullable', 'date'],
            'signature_text' => ['nullable', 'string', 'max:160'],
            'slots' => ['required', 'array', 'min:1'],
            'slots.*.slot_index' => ['required', 'integer', 'between:1,12'],
            'slots.*.label' => ['nullable', 'string', 'max:40'],
            'slots.*.starts_at' => ['nullable', 'date_format:H:i'],
            'slots.*.ends_at' => ['nullable', 'date_format:H:i'],
            'entries' => ['nullable', 'array'],
            'entries.*.*.subject_name' => ['nullable', 'string', 'max:140'],
            'entries.*.*.class_name' => ['nullable', 'string', 'max:100'],
            'entries.*.*.room_name' => ['nullable', 'string', 'max:80'],
            'entries.*.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'school_id.required' => trans('teacher_schedule.school_required'),
        ];
    }

    public function attributes(): array
    {
        return [
            'school_id' => trans('teacher_schedule.institution'),
        ];
    }

    private function normalizeTimeValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach (['H:i', 'H:i:s', 'g:i A', 'g:i a', 'h:i A', 'h:i a'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('H:i');
            } catch (\Throwable) {
            }
        }

        return $value;
    }
}
