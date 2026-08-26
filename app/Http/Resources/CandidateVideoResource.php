<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateVideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'video_url'        => $this->video_url,
            'thumbnail_url'    => $this->thumbnail_url,
            'duration_seconds' => $this->duration_seconds,
            'status'           => $this->status,
        ];
    }
}
