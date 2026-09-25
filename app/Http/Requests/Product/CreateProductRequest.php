<?php

namespace App\Http\Requests\Product;

use App\DTOs\Product\ProductDto;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',

            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'long_description' => 'nullable|string',

            'benefits' => 'nullable|array',
            'benefits.*' => 'string',

            'specifications' => 'nullable|array',

            'usage_tips' => 'nullable|array',
            'usage_tips.*' => 'string',

            'seo_tags' => 'nullable|array',
            'seo_tags.*' => 'string',

            'rook_status' => 'nullable|string|in:accepted,processing,completed,failed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du produit est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'price.numeric' => 'Le prix doit être numérique.',
            'price.min' => 'Le prix doit être supérieur ou égal à 0.',
            'rook_status.in' => 'Statut invalide.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'description' => $this->nullIfEmpty('description'),
            'seo_title' => $this->nullIfEmpty('seo_title'),
            'meta_description' => $this->nullIfEmpty('meta_description'),
            'long_description' => $this->nullIfEmpty('long_description'),
        ]);
    }

    private function nullIfEmpty(string $key): ?string
    {
        $v = $this->input($key);
        if ($v === null) return null;
        $v = trim((string) $v);
        return $v === '' ? null : $v;
    }

    public function toDto(): ProductDto
    {
        return new ProductDto(
            id: null,
            name: $this->input('name'),
            description: $this->input('description'),
            price: $this->input('price') !== null ? (float) $this->input('price') : null,
            seo_title: $this->input('seo_title'),
            meta_description: $this->input('meta_description'),
            long_description: $this->input('long_description'),
            benefits: $this->input('benefits', []),
            specifications: $this->input('specifications', []),
            usage_tips: $this->input('usage_tips', []),
            seo_tags: $this->input('seo_tags', []),
            rook_status: $this->input('rook_status', 'processing'),
        );
    }
}