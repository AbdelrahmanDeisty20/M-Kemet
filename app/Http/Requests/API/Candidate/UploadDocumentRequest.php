<?php

namespace App\Http\Requests\API\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type' => ['required', 'string', 'in:personal_photo,national_id,passport,cv'],
            'file'          => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:10240'], // 10MB
        ];
    }

    public function attributes(): array
    {
        return [
            'document_type' => __('validation.attributes.document_type'),
            'file'          => __('validation.attributes.file'),
        ];
    }
}
