<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidatePersonalInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'birth_date'      => $this->birth_date?->format('Y-m-d'),
            'age'             => $this->age,
            'gender'          => new GenderResource($this->whenLoaded('genderRelation')),
            'current_country' => new CountryResource($this->whenLoaded('currentCountry')),
        ];
    }
}
