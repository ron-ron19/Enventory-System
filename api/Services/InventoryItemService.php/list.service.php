<?php

declare(strict_types=1);

function listItems(
    PDO $pdo,
    array $filter = [],
    array $options = []
): array {
    $categoryId = $filter['categoryId'] ?? null;
    $name = $filter['name'] ?? null;
    $description = $filter['description'] ?? null;
    $quantity = $filter['quantity'] ?? null;
    $createdAt = $filter['createdAt'] ?? null;
    $unit = $filter['unit'] ?? null;

    $lastItemId = $options['lastItemId'] ?? null;
    $sortBy = $options['sortBy'] ?? 'quantity';
    $order = strtolower($options['order'] ?? 'asc') === 'desc'
        ? 'DESC'
        : 'ASC';

    $allowedSortColumns = [
        'id',
        'name',
        'description',
        'quantity',
        'unit',
        'categoryId',
        'createdAt',
        'updatedAt',
    ];

    if (!in_array($sortBy, $allowedSortColumns, true)) {
        $sortBy = 'quantity';
    }

    $where = [];
    $params = [];

    if ($name !== null) {
        $where[] = 'LOWER(i.name) LIKE LOWER(:name)';
        $params[':name'] = '%' . $name . '%';
    }

    if ($description !== null) {
        $where[] = 'LOWER(i.description) LIKE LOWER(:description)';
        $params[':description'] = '%' . $description . '%';
    }

    if ($quantity !== null) {
        $where[] = 'i.quantity = :quantity';
        $params[':quantity'] = $quantity;
    }

    if ($categoryId !== null) {
        $where[] = 'i.category_id IN (' . implode(
            ',',
            array_map(
                fn($key) => ':category_' . $key,
                array_keys($categoryId)
            )
        ) . ')';

        foreach ($categoryId as $key => $value) {
            $params[':category_' . $key] = $value;
        }
    }

    if ($unit !== null) {
        $where[] = 'i.unit IN (' . implode(
            ',',
            array_map(
                fn($key) => ':unit_' . $key,
                array_keys($unit)
            )
        ) . ')';

        foreach ($unit as $key => $value) {
            $params[':unit_' . $key] = $value;
        }
    }

    if ($createdAt !== null) {
        $where[] = 'i.created_at >= :created_at';
        $params[':created_at'] = $createdAt;
    }

    if ($lastItemId !== null) {
        $where[] = 'i.id >= :last_item_id';
        $params[':last_item_id'] = $lastItemId;
    }

    $sql = '
        SELECT
            i.*,
            c.id AS category_id,
            c.name AS category_name
        FROM inventory_items i
        LEFT JOIN inventory_item_categories c
            ON c.id = i.category_id
    ';

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= " ORDER BY i.$sortBy $order";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return array_map(
        'toInventoryItemDto',
        $stmt->fetchAll(PDO::FETCH_ASSOC)
    );
}
