<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email', 'exists:users,email'],
            'code'     => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->symbols()],
        ];
    }

    public function attributes(): array
    {
        return [
            'email'    => __('validation.attributes.email'),
            'code'     => __('validation.attributes.code'),
            'password' => __('validation.attributes.password'),
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email'       => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.exists'      => __('validation.exists', ['attribute' => __('validation.attributes.email')]),
            'code.required'     => __('validation.required', ['attribute' => __('validation.attributes.code')]),
            'code.size'         => __('validation.size.string', ['attribute' => __('validation.attributes.code'), 'size' => 6]),
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
            'password.min'      => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]),
            'password.confirmed'=> __('validation.confirmed', ['attribute' => __('validation.attributes.password')]),
        ];
    }
}
