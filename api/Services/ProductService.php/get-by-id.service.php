<?php

declare(strict_types=1);

function getById(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare(
        'SELECT
            p.*,
            c.id AS category_id,
            c.name AS category_name,
            r.id AS recipe_item_id,
            r.quantity AS recipe_quantity,
            i.id AS inventory_item_id,
            i.name AS inventory_item_name,
            i.quantity AS inventory_item_quantity,
            ic.id AS inventory_category_id,
            ic.name AS inventory_category_name
         FROM products p
         LEFT JOIN product_categories c
            ON c.id = p.category_id
         LEFT JOIN recipe_items r
            ON r.product_id = p.id
         LEFT JOIN inventory_items i
            ON i.id = r.inventory_item_id
         LEFT JOIN inventory_item_categories ic
            ON ic.id = i.category_id
         WHERE p.id = :id'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 0) {
        throw new AppError(
            'NOT_FOUND',
            'Product not found'
        );
    }

    $product = $rows[0];

    $product['category'] = $product['category_id'] !== null
        ? [
            'id' => $product['category_id'],
            'name' => $product['category_name'],
        ]
        : null;

    $product['recipeItems'] = [];

    foreach ($rows as $row) {
        if ($row['recipe_item_id'] === null) {
            continue;
        }

        $product['recipeItems'][] = [
            'id' => $row['recipe_item_id'],
            'quantity' => $row['recipe_quantity'],
            'inventoryItem' => [
                'id' => $row['inventory_item_id'],
                'name' => $row['inventory_item_name'],
                'quantity' => $row['inventory_item_quantity'],
                'category' => $row['inventory_category_id'] !== null
                    ? [
                        'id' => $row['inventory_category_id'],
                        'name' => $row['inventory_category_name'],
                    ]
                    : null,
            ],
        ];
    }

    return toProductWithInventoryItemsDto($product);
}
