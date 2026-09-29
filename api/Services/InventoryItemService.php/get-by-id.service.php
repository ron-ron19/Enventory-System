<?php

declare(strict_types=1);

function getById(PDO $pdo, int $id): array
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

    $stmt->execute([
        ':id' => $id,
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result === false) {
        throw new AppError(
            'NOT_FOUND',
            'Item Not Found'
        );
    }

    return [
        'createdAt' => $result['createdAt'] ?? null,
        'updatedAt' => $result['updatedAt'] ?? null,
        ...toInventoryItemDto($result),
    ];
}
