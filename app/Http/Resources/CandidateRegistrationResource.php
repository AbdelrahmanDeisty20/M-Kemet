<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'user_type'         => $this->user_type,
            'account_status'    => $this->status,
            'country'           => new CountryResource($this->whenLoaded('country')),
            'candidate_profile' => new CandidateProfileResource($this->whenLoaded('candidateProfile')),
            'created_at'        => $this->created_at?->toDateTimeString(),
        ];
    }
}
