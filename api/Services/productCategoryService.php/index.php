<?php

declare(strict_types=1);

class ProductCategoriesService
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function create(array $data): array
    {
        return create($this->pdo, $data);
    }

    public function update(int $id, array $data): array
    {
        return update($this->pdo, $id, $data);
    }

    public function delete(int $id): array
    {
        return remove($this->pdo, $id);
    }

    public function getById(int $id): array
    {
        return getById($this->pdo, $id);
    }

    public function list(array $query = []): array
    {
        return listCategories($this->pdo, $query);
    }

    public function assignProducts(int $id, array $data): array
    {
        return assignProducts($this->pdo, $id, $data);
    }

    public function unassignProducts(int $id, array $data): array
    {
        return unassignProducts($this->pdo, $id, $data);
    }
}

$productCategoriesService = new ProductCategoriesService($pdo);
