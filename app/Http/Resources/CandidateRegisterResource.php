<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateRegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'  => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'current_country' => new CountryResource($this->whenLoaded('currentCountry')),
            'gender' => new GenderResource($this->whenLoaded('gender')),
            
        ];
    }
}
