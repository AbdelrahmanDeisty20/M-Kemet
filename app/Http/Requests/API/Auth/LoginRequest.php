<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone'    => __('validation.attributes.phone'),
            'password' => __('validation.attributes.password'),
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'    => __('validation.required', ['attribute' => __('validation.attributes.phone')]),
            'phone.unique'       => __('validation.unique', ['attribute' => __('validation.attributes.phone')]),
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
        ];
    }
}
