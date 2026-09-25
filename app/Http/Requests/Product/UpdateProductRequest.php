<?php

namespace App\Http\Requests\Product;

use App\DTOs\Product\ProductDto;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'seo_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'long_description' => ['sometimes', 'nullable', 'string'],
            'benefits' => ['sometimes', 'array'],
            'benefits.*' => ['string'],
            'specifications' => ['sometimes', 'array'],
            'usage_tips' => ['sometimes', 'array'],
            'seo_tags' => ['sometimes', 'array'],
            'rook_status' => ['sometimes', 'string', 'in:accepted,processing,completed,failed'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'price.numeric' => 'Le prix doit être numérique.',
            'rook_status.in' => 'Statut invalide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['name', 'description', 'seo_title', 'meta_description', 'long_description'] as $key) {
            if ($this->has($key)) {
                $v = $this->input($key);
                $this->merge([$key => $v === null ? null : trim((string) $v)]);
            }
        }
    }

    public function toDto(?Product $product = null): ProductDto
    {
        return new ProductDto(
            id: $product?->id,
            name: $this->input('name', $product?->name),
            description: $this->input('description', $product?->description),
            price: $this->input('price') !== null ? (float) $this->input('price') : $product?->price,
            seo_title: $this->input('seo_title', $product?->seo_title),
            meta_description: $this->input('meta_description', $product?->meta_description),
            long_description: $this->input('long_description', $product?->long_description),
            benefits: $this->input('benefits', $product?->benefits),
            specifications: $this->input('specifications', $product?->specifications),
            usage_tips: $this->input('usage_tips', $product?->usage_tips),
            seo_tags: $this->input('seo_tags', $product?->seo_tags),
            rook_job_id: $product?->rook_job_id,
            rook_status: $this->input('rook_status', $product?->rook_status),
        );
    }
}