<?php

declare(strict_types=1);

function listCategories(PDO $pdo, array $query = []): array
{
    $name = $query['filter']['name'] ?? null;

    $sql = '
        SELECT
            c.*,
            p.id AS product_id,
            p.name AS product_name,
            r.id AS recipe_item_id,
            r.inventory_item_id,
            i.id AS inventory_item_id_value,
            i.name AS inventory_item_name
        FROM product_categories c
        LEFT JOIN products p
            ON p.category_id = c.id
        LEFT JOIN recipe_items r
            ON r.product_id = p.id
        LEFT JOIN inventory_items i
            ON i.id = r.inventory_item_id
    ';

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
                'products' => [],
            ];
        }

        if ($row['product_id'] !== null) {
            $productId = $row['product_id'];

            if (!isset($categories[$categoryId]['products'][$productId])) {
                $categories[$categoryId]['products'][$productId] = [
                    'id' => $productId,
                    'name' => $row['product_name'],
                    'recipeItems' => [],
                ];
            }

            if ($row['recipe_item_id'] !== null) {
                $categories[$categoryId]['products'][$productId]['recipeItems'][] = [
                    'id' => $row['recipe_item_id'],
                    'inventoryItemId' => $row['inventory_item_id'],
                    'inventoryItem' => [
                        'id' => $row['inventory_item_id_value'],
                        'name' => $row['inventory_item_name'],
                    ],
                ];
            }
        }
    }

    foreach ($categories as &$category) {
        $category['products'] = array_values($category['products']);
    }

    return array_map(
        'toProductCategoryWithProductsDto',
        array_values($categories)
    );
}
