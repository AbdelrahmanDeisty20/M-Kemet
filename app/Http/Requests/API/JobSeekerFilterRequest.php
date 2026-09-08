<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class JobSeekerFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id'      => ['nullable', 'integer', 'exists:countries,id'],
            'profession_id'   => ['nullable', 'integer', 'exists:professions,id'],
            'gender_id'       => ['nullable', 'integer', 'exists:genders,id'],
            'passport_status' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $hasAnyFilter = $this->filled('country_id')
                || $this->filled('profession_id')
                || $this->filled('gender_id')
                || $this->filled('passport_status');

            if (!$hasAnyFilter) {
                $validator->errors()->add('filter', __('messages.filter_option_required'));
            }
        });
    }
}
