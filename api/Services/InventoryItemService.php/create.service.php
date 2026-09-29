<?php

declare(strict_types=1);

function create(PDO $pdo, array $data): array
{
    $categoryId = $data['categoryId'] ?? null;

    if ($categoryId) {
        $stmt = $pdo->prepare(
            'SELECT id
             FROM inventory_item_categories
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $categoryId,
        ]);

        if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
            throw new AppError(
                'NOT_FOUND',
                'Inventory item category not found'
            );
        }
    }

    unset($data['categoryId']);

    $columns = array_keys($data);
    $placeholders = array_map(
        fn ($column) => ':' . $column,
        $columns
    );

    $sql = sprintf(
        'INSERT INTO inventory_items (%s) VALUES (%s)',
        implode(', ', $columns),
        implode(', ', $placeholders)
    );

    $stmt = $pdo->prepare($sql);

    $params = [];
    foreach ($data as $column => $value) {
        $params[':' . $column] = $value;
    }

    $stmt->execute($params);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'SELECT *
         FROM inventory_items
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    return toInventoryItemDto(
        $stmt->fetch(PDO::FETCH_ASSOC)
    );
}
