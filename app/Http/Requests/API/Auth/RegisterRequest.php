<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['nullable', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:20', 'unique:users,phone'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'confirmed', Password::min(8)->mixedCase()->symbols()],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'         => __('validation.attributes.name'),
            'company_name' => __('validation.attributes.company_name'),
            'phone'        => __('validation.attributes.phone'),
            'email'        => __('validation.attributes.email'),
            'password'     => __('validation.attributes.password'),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => __('validation.required', ['attribute' => __('validation.attributes.name')]),
            'name.string'       => __('validation.string', ['attribute' => __('validation.attributes.name')]),
            'phone.required'    => __('validation.required', ['attribute' => __('validation.attributes.phone')]),
            'phone.unique'      => __('validation.unique', ['attribute' => __('validation.attributes.phone')]),
            'email.required'    => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email'       => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.unique'      => __('validation.unique', ['attribute' => __('validation.attributes.email')]),
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
            'password.min'      => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 8]),
            'password.confirmed'=> __('validation.confirmed', ['attribute' => __('validation.attributes.password')]),
        ];
    }
}
