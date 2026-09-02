<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'video_url'        => $this->video_url,
            'thumbnail_url'    => $this->thumbnail_url,
            'duration_seconds' => $this->duration_seconds,
            'file_size_mb'     => $this->file_size_mb,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
