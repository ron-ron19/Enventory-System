<?php

declare(strict_types=1);

function update(PDO $pdo, int $id, array $data): array
{
    $stmt = $pdo->prepare(
        'SELECT id
         FROM inventory_item_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        throw new AppError(
            'NOT_FOUND',
            'Item category not found'
        );
    }

    if (!empty($data['name'])) {
        $stmt = $pdo->prepare(
            'UPDATE inventory_item_categories
             SET name = :name
             WHERE id = :id'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':id' => $id,
        ]);
    }

    $stmt = $pdo->prepare(
        'SELECT *
         FROM inventory_item_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    return toInventoryItemCategoryDto(
        $stmt->fetch(PDO::FETCH_ASSOC)
    );
}
