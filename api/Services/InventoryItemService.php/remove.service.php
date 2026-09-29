<?php

declare(strict_types=1);

function remove(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare(
        'SELECT
            i.*,
            c.id AS category_id,
            c.name AS category_name
         FROM inventory_items i
         LEFT JOIN inventory_item_categories c
            ON c.id = i.category_id
         WHERE i.id = :id
         LIMIT 1'
    );

    $stmt->execute([':id' => $id]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item === false) {
        throw new AppError(
            'NOT_FOUND',
            'Item not found'
        );
    }

    $stmt = $pdo->prepare(
        'SELECT product_id
         FROM recipe_items
         WHERE inventory_item_id = :item_id'
    );

    $stmt->execute([':item_id' => $id]);

    $recipeItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($recipeItems) > 0) {
        throw new AppError(
            'ITEM_IN_USE',
            'This item cannot be deleted because it is associated with '
            . count($recipeItems)
            . ' products',
            array_map(
                fn(array $recipeItem) => [
                    'productId' => $recipeItem['product_id'],
                ],
                $recipeItems
            )
        );
    }

    $stmt = $pdo->prepare(
        'DELETE FROM inventory_items
         WHERE id = :id'
    );

    $stmt->execute([':id' => $id]);

    return [
        'createdAt' => $item['created_at'],
        'updatedAt' => $item['updated_at'],
        ...toInventoryItemDto($item),
    ];
}
