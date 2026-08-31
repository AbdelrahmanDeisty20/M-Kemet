<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->user_type === 'company') {
            return (new CompanyRegisterResource($this->loadMissing('company')))->toArray($request);
        }

        return (new CandidateRegisterResource($this->loadMissing(['country', 'candidateProfile.genderRelation'])))->toArray($request);
    }
}
