<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateCareerInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'qualification'    => $this->qualification,
            'experience_years' => $this->experience_years,
            'expected_salary'  => $this->expected_salary,
            'willing_to_travel' => $this->willing_to_travel,
            'languages'        => $this->languages ?? [],
            'summary'          => $this->summary,
            'professions'      => ProfessionResource::collection($this->whenLoaded('professions')),
            'target_countries' => CountryResource::collection($this->whenLoaded('targetCountries')),
        ];
    }
}
