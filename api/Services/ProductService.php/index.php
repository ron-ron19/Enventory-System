<?php

declare(strict_types=1);

class ProductsService
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function create(array $data): array
    {
        return create($this->pdo, $data);
    }

    public function list(array $filter = [], array $options = []): array
    {
        return listProducts($this->pdo, $filter, $options);
    }

    public function delete(int $id): array
    {
        return remove($this->pdo, $id);
    }

    public function update(int $id, array $data): array
    {
        return update($this->pdo, $id, $data);
    }

    public function getById(int $id): array
    {
        return getById($this->pdo, $id);
    }

    public function deductStockForProduct(int $id, array $data): array
    {
        return deductStockForProduct($this->pdo, $id, $data);
    }
}

$productsService = new ProductsService($pdo);
