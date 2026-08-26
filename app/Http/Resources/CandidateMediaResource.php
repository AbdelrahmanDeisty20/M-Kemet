<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user ?? null;

        return [
            'personal_photo' => $user?->documents?->where('document_type', 'personal_photo')->first()?->secure_url,
            'cv'             => $user?->documents?->where('document_type', 'cv')->first()?->secure_url,
            'passport'       => $user?->documents?->where('document_type', 'passport')->first()?->secure_url,
            'national_id'    => $user?->documents?->where('document_type', 'national_id')->first()?->secure_url,
            'intro_video'    => $user?->video ? new CandidateVideoResource($user->video) : null,
        ];
    }
}
