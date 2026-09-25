<?php

namespace App\DTOs\Product;

class ProductDto
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?float $price = null,
        public readonly ?string $seo_title = null,
        public readonly ?string $meta_description = null,
        public readonly ?string $long_description = null,
        public readonly ?array $benefits = null,
        public readonly ?array $specifications = null,
        public readonly ?array $usage_tips = null,
        public readonly ?array $seo_tags = null,
        public readonly ?string $rook_job_id = null,
        public readonly ?string $rook_status = null,
        public readonly ?string $created_at = null,
    ) {}

    public function toArray(): array
    {
        $data = [
            'name'             => $this->name,
            'description'      => $this->description,
            'price'            => $this->price,
            'seo_title'        => $this->seo_title,
            'meta_description' => $this->meta_description,
            'long_description' => $this->long_description,
            'benefits'         => $this->benefits,
            'specifications'   => $this->specifications,
            'usage_tips'       => $this->usage_tips,
            'seo_tags'         => $this->seo_tags,
            'rook_job_id'      => $this->rook_job_id,
            'rook_status'      => $this->rook_status,
        ];

        return array_filter($data, fn ($v) => $v !== null);
    }
}