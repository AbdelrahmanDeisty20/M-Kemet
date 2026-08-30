<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateRegisterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name'            => $this->name,
            'phone'           => $this->phone,
            'email'           => $this->email,
            'current_country' => new CountryResource($this->whenLoaded('country')),
            'gender'          => new GenderResource($this->candidateProfile?->gender),
        ];
    }
}
