<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTeacherScheduleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // معامل المسار قد يكون نموذجاً (ربط ضمني) أو قيمة؛ نستخرج المفتاح في الحالتين.
        $param = $this->route('teacher_schedule') ?? $this->route('teacherSchedule') ?? $this->route('id');

        $this->merge([
            'id' => is_object($param) ? $param->getKey() : $param,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:teacher_schedules,id'],
        ];
    }
}
