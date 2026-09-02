<?php

namespace App\Http\Requests\API\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UploadVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'video'            => ['required', 'file', 'mimes:mp4,mov,avi,webm', 'max:51200'], // 50MB
            'duration_seconds' => ['nullable', 'integer', 'max:300'],
        ];
    }

    public function attributes(): array
    {
        return [
            'video'            => __('validation.attributes.video'),
            'duration_seconds' => __('validation.attributes.duration_seconds'),
        ];
    }
}
