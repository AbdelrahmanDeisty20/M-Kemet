<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => __('validation.attributes.email'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email'    => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.exists'   => __('validation.exists', ['attribute' => __('validation.attributes.email')]),
        ];
    }
}
