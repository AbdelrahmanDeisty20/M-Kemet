<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class JobSeekerSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword' => ['required', 'string'],
        ];
    }
}
