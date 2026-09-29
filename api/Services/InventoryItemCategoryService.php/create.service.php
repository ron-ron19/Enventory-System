<?php

declare(strict_types=1);

function create(PDO $pdo, array $data): array
{
    $stmt = $pdo->prepare(
        'INSERT INTO inventory_item_categories (name)
         VALUES (:name)'
    );

    $stmt->execute([
        ':name' => $data['name'],
    ]);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'SELECT *
         FROM inventory_item_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return toInventoryItemCategoryDto($result);
}
