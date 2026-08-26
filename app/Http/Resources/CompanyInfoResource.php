<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'company_name'               => $this->company_name,
            'commercial_register_number' => $this->commercial_register_number,
            'industry'                   => $this->industry,
            'country'                    => new CountryResource($this->whenLoaded('country')),
            'city'                       => $this->city,
            'address'                    => $this->address,
        ];
    }
}
