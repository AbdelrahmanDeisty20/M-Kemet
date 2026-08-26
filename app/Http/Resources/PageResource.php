<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slug'             => $this->slug,
            'title'            => $this->title, // Dynamic localized attribute via model accessor
            'content'          => $this->content, // Dynamic localized attribute via model accessor
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
        ];
    }
}
