<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QualificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (is_string($this->resource)) {
            return [
                'id'   => null,
                'name' => $this->resource,
                'code' => null,
            ];
        }

        return [
            'id'   => $this->id,
            'name' => $this->name ?? $this->name_ar ?? null,
            'code' => $this->code ?? null,
        ];
    }
}
