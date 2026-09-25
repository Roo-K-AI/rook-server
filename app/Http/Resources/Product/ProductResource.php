<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,

            'seo_title' => $this->seo_title,
            'meta_description' => $this->meta_description,
            'long_description' => $this->long_description,

            'benefits' => $this->benefits ?? [],
            'specifications' => $this->specifications ?? [],
            'usage_tips' => $this->usage_tips ?? [],
            'seo_tags' => $this->seo_tags ?? [],

            'rook_job_id' => $this->rook_job_id,
            'rook_status' => $this->rook_status,

            'dates' => [
                'created_at' => $this->created_at?->format('Y-m-d H:i'),
                'updated_at' => $this->updated_at?->format('Y-m-d H:i'),
                'time_ago'   => $this->created_at?->diffForHumans(),
            ],
        ];
    }
}