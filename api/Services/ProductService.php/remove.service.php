<?php

declare(strict_types=1);

function remove(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare(
        'SELECT id
         FROM products
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([':id' => $id]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        throw new AppError(
            'NOT_FOUND',
            'Product not found'
        );
    }

    try {
        $pdo->beginTransaction();

        // Delete recipe items first, matching the Prisma transaction.
        $stmt = $pdo->prepare(
            'DELETE FROM recipe_items
             WHERE product_id = :product_id'
        );

        $stmt->execute([
            ':product_id' => $id,
        ]);

        // Fetch the product before deleting it so the response
        // can contain the same product data.
        $stmt = $pdo->prepare(
            'SELECT
                p.*,
                c.id AS category_id,
                c.name AS category_name
             FROM products p
             LEFT JOIN product_categories c
                ON c.id = p.category_id
             WHERE p.id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        // Recipe items are already deleted, so this is empty.
        $product['recipeItems'] = [];

        $stmt = $pdo->prepare(
            'DELETE FROM products
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $pdo->commit();

        return [
            'createdAt' => $product['created_at'] ?? null,
            'updatedAt' => $product['updated_at'] ?? null,
            ...toProductWithInventoryItemsDto($product),
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
