<?php

namespace App\Http\Requests;

use App\Models\Recruitment\JobPost;
use Illuminate\Foundation\Http\FormRequest;

class DestroyJobPostRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $routeValue = $this->route('JobPost') ?? $this->route('jobPost');
        $id = null;

        if ($routeValue instanceof JobPost) {
            $id = $routeValue->id;
        } elseif (is_numeric($routeValue)) {
            $id = (int) $routeValue;
        } elseif (is_string($routeValue) && $routeValue !== '') {
            $id = JobPost::query()->where('slug', $routeValue)->value('id');
        }

        $this->merge([
            'id' => $id,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:job_posts,id'],
        ];
    }
}
