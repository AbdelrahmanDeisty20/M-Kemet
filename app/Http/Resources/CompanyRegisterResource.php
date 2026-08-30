<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRegisterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'company' => new CompanyInfoResource($this->whenLoaded('company')),
            'status'       => $this->status,
            'phone'        => $this->phone,
            'email'        => $this->email,
            
        ];
    }
}
