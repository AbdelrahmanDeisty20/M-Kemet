<?php

namespace App\Http\Requests\API\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCandidateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['nullable', 'string', 'max:255'],
            'birth_date'         => ['nullable', 'date', 'before:today'],
            'gender_id'          => ['nullable', 'integer', 'exists:genders,id'],
            'current_country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'qualification'      => ['nullable', 'string', 'max:255'],
            'sub_specialization' => ['nullable', 'string', 'max:255'],
            'experience_years'   => ['nullable', 'integer', 'min:0', 'max:50'],
            'experience_level_id'=> ['nullable', 'integer', 'exists:experience_levels,id'],
            'expected_salary'    => ['nullable', 'numeric', 'min:0'],
            'willing_to_travel'  => ['nullable', 'boolean'],
            'languages'          => ['nullable', 'array'],
            'languages.*'        => ['string', 'max:100'],
            'skills'             => ['nullable', 'array'],
            'skills.*'           => ['string', 'max:100'],
            'summary'            => ['nullable', 'string', 'max:2000'],
            'profession_id'      => ['nullable', 'integer', 'exists:professions,id'],
            'target_country_ids' => ['nullable', 'array'],
            'target_country_ids.*' => ['integer', 'exists:countries,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'               => __('validation.attributes.name'),
            'birth_date'         => __('validation.attributes.birth_date'),
            'gender_id'          => __('validation.attributes.gender_id'),
            'current_country_id' => __('validation.attributes.current_country_id'),
            'qualification'      => __('validation.attributes.qualification'),
            'sub_specialization' => __('validation.attributes.sub_specialization'),
            'experience_years'   => __('validation.attributes.experience_years'),
            'experience_level_id'=> __('validation.attributes.experience_level_id'),
            'expected_salary'    => __('validation.attributes.expected_salary'),
            'willing_to_travel'  => __('validation.attributes.willing_to_travel'),
            'languages'          => __('validation.attributes.languages'),
            'languages.*'        => __('validation.attributes.languages.*'),
            'skills'             => __('validation.attributes.skills'),
            'skills.*'           => __('validation.attributes.skills.*'),
            'summary'            => __('validation.attributes.summary'),
            'profession_id'      => __('validation.attributes.profession_id'),
            'target_country_ids' => __('validation.attributes.target_country_ids'),
            'target_country_ids.*' => __('validation.attributes.target_country_ids.*'),
        ];
    }
}
