<?php

declare(strict_types=1);

function listCategories(PDO $pdo, array $query = []): array
{
    $name = $query['filter']['name'] ?? null;

    $sql = 'SELECT c.*, i.*
            FROM inventory_item_categories c
            LEFT JOIN inventory_items i ON i.category_id = c.id';

    $params = [];

    if ($name !== null) {
        $sql .= ' WHERE LOWER(c.name) LIKE LOWER(:name)';
        $params[':name'] = '%' . $name . '%';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $categories = [];

    foreach ($rows as $row) {
        $categoryId = $row['id'];

        if (!isset($categories[$categoryId])) {
            $categories[$categoryId] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'inventoryItems' => [],
            ];
        }

        if ($row['category_id'] !== null) {
            $categories[$categoryId]['inventoryItems'][] = $row;
        }
    }

    return array_map(
        'toInventoryItemCategoryWithItemsDto',
        array_values($categories)
    );
}
