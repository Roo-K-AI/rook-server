<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public $collects = ProductResource::class;

    public function toArray($request): array
    {
        return [
            'data' => $this->collection,   // déjà transformée via $collects
            'meta' => [
                'total' => $this->collection->count(),
            ],
        ];
    }
}