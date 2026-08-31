<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                         => $this->id,
            'user_id'                    => $this->user_id,
            'company_name'               => $this->company_name,
            'commercial_register_number' => $this->commercial_register_number,
            'industry'                   => $this->industry,
            'country'                    => new CountryResource($this->whenLoaded('country')),
            'city'                       => $this->city,
            'address'                    => $this->address,
            'status'                     => $this->status,
        ];
    }
}
