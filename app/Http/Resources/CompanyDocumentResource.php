<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'document_type' => $this->document_type,
            'secure_url'    => $this->secure_url,
            'is_verified'   => $this->is_verified,
        ];
    }
}
