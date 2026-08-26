<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user'             => new UserResource($this->whenLoaded('user')),
            'company_info'     => new CompanyInfoResource($this),
            'documents'        => CompanyDocumentResource::collection($this->whenLoaded('documents')),
            'status'           => $this->status, // pending, approved, rejected
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}
