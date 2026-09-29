<?php

declare(strict_types=1);

function create(PDO $pdo, array $data): array
{
    $stmt = $pdo->prepare(
        'INSERT INTO product_categories (name)
         VALUES (:name)'
    );

    $stmt->execute([
        ':name' => $data['name'],
    ]);

    $id = (int) $pdo->lastInsertId();

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
