<?php

namespace App\Http\Requests\API\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:10240'], // 10MB
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => __('validation.attributes.file'),
        ];
    }
}
