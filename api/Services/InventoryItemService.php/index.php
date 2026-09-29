<?php

declare(strict_types=1);

class InventoryItemsService
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

    public function delete(int $id): void
    {
        remove($this->pdo, $id);
    }

    public function list(array $query = []): array
    {
        return listItems($this->pdo, $query);
    }

    public function getById(int $id): array
    {
        return getById($this->pdo, $id);
    }

    public function stockIn(int $id, array $data): array
    {
        return stockIn($this->pdo, $id, $data);
    }

    public function stockOut(int $id, array $data): array
    {
        return stockOut($this->pdo, $id, $data);
    }
}

$inventoryItemsService = new InventoryItemsService($pdo);
