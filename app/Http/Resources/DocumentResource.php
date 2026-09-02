<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'document_type'    => $this->document_type,
            'file_path'        => $this->file_path,
            'url'              => $this->secure_url,
            'is_approved'      => $this->is_approved,
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
