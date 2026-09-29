<?php

declare(strict_types=1);

function update(PDO $pdo, int $id, array $data): array
{
    $stmt = $pdo->prepare(
        'SELECT id
         FROM product_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        throw new AppError(
            'NOT_FOUND',
            'Product category not found'
        );
    }

    $stmt = $pdo->prepare(
        'UPDATE product_categories
         SET name = :name
         WHERE id = :id'
    );

    $stmt->execute([
        ':name' => $data['name'],
        ':id' => $id,
    ]);

    $stmt = $pdo->prepare(
        'SELECT *
         FROM product_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([
        ':id' => $id,
    ]);

    return toProductCategoryDto(
        $stmt->fetch(PDO::FETCH_ASSOC)
    );
}
