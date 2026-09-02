<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'user_id'                => $this->user_id,
            'name'                   => $this->user?->name,
            'email'                  => $this->user?->email,
            'phone'                  => $this->user?->phone,
            'birth_date'             => $this->birth_date?->format('Y-m-d'),
            'age'                    => $this->age,
            'gender'                 => new GenderResource($this->whenLoaded('genderRelation')),
            'current_country'        => new CountryResource($this->whenLoaded('currentCountry')),
            'qualification'          => $this->qualification,
            'sub_specialization'     => $this->sub_specialization,
            'experience_years'       => $this->experience_years,
            'experience_level'       => new ExperienceLevelResource($this->whenLoaded('experienceLevel')),
            'expected_salary'        => $this->expected_salary,
            'willing_to_travel'      => $this->willing_to_travel,
            'languages'              => $this->languages,
            'skills'                 => $this->skills,
            'summary'                => $this->summary,
            'completion_percentage'  => $this->completion_percentage,
            'profession'             => new ProfessionResource($this->whenLoaded('profession')),
            'target_countries'       => CountryResource::collection($this->whenLoaded('targetCountries')),
            'documents'              => DocumentResource::collection($this->whenLoaded('documents')),
            'video'                  => new VideoResource($this->whenLoaded('video')),
            'status'                 => $this->status,
        ];
    }
}
