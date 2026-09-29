<?php

declare(strict_types=1);

function getById(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare(
        'SELECT *
         FROM inventory_item_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category === false) {
        throw new AppError(
            'NOT_FOUND',
            'Item category not found'
        );
    }

    $stmt = $pdo->prepare(
        'SELECT *
         FROM inventory_items
         WHERE category_id = :category_id'
    );

    $stmt->execute([
        ':category_id' => $id,
    ]);

    $category['inventoryItems'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return toInventoryItemCategoryWithItemsDto($category);
}
