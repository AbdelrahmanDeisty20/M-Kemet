<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user'             => new UserResource($this->whenLoaded('user')),
            'personal_info'    => new CandidatePersonalInfoResource($this->whenLoaded('personalInfo')),
            'career_info'      => new CandidateCareerInfoResource($this->whenLoaded('careerInfo')),
            'media'            => new CandidateMediaResource($this->whenLoaded('media')),
            'profile_status'   => $this->status, // pending, approved, rejected
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}
