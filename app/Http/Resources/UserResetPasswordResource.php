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

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'email' => $this->email,
            'code'  => $this->code,
            'user'  => new UserResource($this->resource),
        ];
    }
}
