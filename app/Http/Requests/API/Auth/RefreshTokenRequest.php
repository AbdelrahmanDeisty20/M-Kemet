<?php

namespace App\Http\Requests\API\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'refresh_token' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'refresh_token' => __('validation.attributes.refresh_token'),
        ];
    }

    public function messages(): array
    {
        return [
            'refresh_token.required' => __('validation.required', ['attribute' => __('validation.attributes.refresh_token')]),
        ];
    }
}
