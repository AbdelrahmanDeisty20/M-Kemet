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
            'id'                 => $this->id,
            'user_id'            => $this->user_id,
            'birth_date'         => $this->birth_date?->format('Y-m-d'),
            'age'                => $this->age,
            'gender'             => new GenderResource($this->whenLoaded('genderRelation')),
            'current_country'    => new CountryResource($this->whenLoaded('currentCountry')),
            'qualification'      => $this->qualification,
            'experience_years'   => $this->experience_years,
            'expected_salary'    => $this->expected_salary,
            'willing_to_travel'  => $this->willing_to_travel,
            'languages'          => $this->languages,
            'summary'            => $this->summary,
            'status'             => $this->status,
        ];
    }
}
