<?php

declare(strict_types=1);

function update(PDO $pdo, int $id, array $data): array
{
    $stmt = $pdo->prepare(
        'SELECT id FROM inventory_items WHERE id = :id LIMIT 1'
    );

    $stmt->execute([':id' => $id]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        throw new AppError('NOT_FOUND', 'Item not found');
    }

    $name = $data['name'] ?? null;
    $description = $data['description'] ?? null;
    $imageUrl = $data['imageUrl'] ?? null;
    $unit = $data['unit'] ?? null;
    $categoryId = $data['categoryId'] ?? null;

    if ($categoryId) {
        $stmt = $pdo->prepare(
            'SELECT id FROM inventory_item_categories WHERE id = :id LIMIT 1'
        );

        $stmt->execute([':id' => $categoryId]);

        if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
            throw new AppError(
                'NOT_FOUND',
                'Inventory item category not found'
            );
        }
    }

    $fields = [];
    $params = [':id' => $id];

    if ($name) {
        $fields[] = 'name = :name';
        $params[':name'] = $name;
    }

    if (array_key_exists('description', $data)) {
        $fields[] = 'description = :description';
        $params[':description'] = $description;
    }

    if (array_key_exists('imageUrl', $data)) {
        $fields[] = 'image_url = :image_url';
        $params[':image_url'] = $imageUrl;
    }

    if ($unit) {
        $fields[] = 'unit = :unit';
        $params[':unit'] = $unit;
    }

    if ($categoryId) {
        $fields[] = 'category_id = :category_id';
        $params[':category_id'] = $categoryId;
    }

    if ($fields) {
        $stmt = $pdo->prepare(
            'UPDATE inventory_items SET ' .
            implode(', ', $fields) .
            ' WHERE id = :id'
        );

        $stmt->execute($params);
    }

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

    return toInventoryItemDto(
        $stmt->fetch(PDO::FETCH_ASSOC)
    );
}
