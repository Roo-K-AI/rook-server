<?php

namespace App\Repositories;

use App\DTOs\Product\ProductDto;
use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(protected Product $model) {}

    public function all()
    {
        return $this->model->orderByDesc('created_at')->get();
    }

    public function find(int $id): Product
    {
        return $this->model->findOrFail($id);
    }

    public function findByCriteria(array $criteria)
    {
        $query = $this->model->newQuery();

        $availableFields = ['id', 'name', 'description', 'rook_status'];

        foreach ($criteria as $field => $value) {
            if ($value === null || $value === '' || !in_array($field, $availableFields, true)) {
                continue;
            }
            $query->where($field, $value);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function create(ProductDto $productDto): Product
    {
        return $this->model->create($productDto->toArray());
    }

    public function update(int $id, ProductDto $productDto): Product
    {
        $product = $this->model->findOrFail($id);
        $product->update($productDto->toArray());
        return $product->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->model->destroy($id);
    }

    public function findByUserId(int $userId)
    {
        return $this->model->where('user_id', $userId)->orderByDesc('created_at')->get();
    }

    public function findByStatus(string $status)
    {
        return $this->model->where('rook_status', $status)->orderByDesc('created_at')->get();
    }
}