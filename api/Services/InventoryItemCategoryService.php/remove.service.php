<?php

declare(strict_types=1);

function remove(PDO $pdo, int $id): array
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

    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category === false) {
        throw new AppError(
            'NOT_FOUND',
            'Item category not found'
        );
    }

    try {
        $pdo->beginTransaction();

        // Unassign all inventory items from this category.
        $stmt = $pdo->prepare(
            'UPDATE inventory_items
             SET category_id = NULL
             WHERE category_id = :category_id'
        );

        $stmt->execute([
            ':category_id' => $id,
        ]);

        // Delete the category.
        $stmt = $pdo->prepare(
            'DELETE FROM inventory_item_categories
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }

    return [
        'id' => $id,
    ];
}
