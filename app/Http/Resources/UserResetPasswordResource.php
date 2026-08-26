<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResetPasswordResource extends JsonResource
{
    protected string $code;

    public function __construct($resource, string $code)
    {
        parent::__construct($resource);
        $this->code = $code;
    }

    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource),
            'code' => $this->code,
        ];
    }
}
