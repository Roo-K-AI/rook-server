<?php

namespace App\Repositories\Interfaces;

use App\DTOs\Product\ProductDto;
use App\Models\Product;

interface ProductRepositoryInterface
{
    public function all();

    public function find(int $id): Product;

    public function findByCriteria(array $criteria);

    public function findByUserId(int $userId);

    public function findByStatus(string $status);

    public function create(ProductDto $productDto): Product;

    public function update(int $id, ProductDto $productDto): Product;

    public function delete(int $id): bool;
}