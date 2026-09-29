<?php

declare(strict_types=1);

function assignProducts(PDO $pdo, int $id, array $data): array
{
    $productIds = $data['productIds'];

    $stmt = $pdo->prepare(
        'SELECT id
         FROM product_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([':id' => $id]);

    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category === false) {
        throw new AppError(
            'NOT_FOUND',
            'Product category not found'
        );
    }

    try {
        $pdo->beginTransaction();

        $updateStmt = $pdo->prepare(
            'UPDATE products
             SET category_id = :category_id
             WHERE id = :product_id'
        );

        $result = [];

        foreach ($productIds as $productId) {
            $updateStmt->execute([
                ':category_id' => $category['id'],
                ':product_id' => $productId,
            ]);

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
                ':id' => $productId,
            ]);

            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product === false) {
                throw new AppError(
                    'NOT_FOUND',
                    'Product not found'
                );
            }

            $recipeStmt = $pdo->prepare(
                'SELECT
                    r.*,
                    i.*
                 FROM recipe_items r
                 LEFT JOIN inventory_items i
                    ON i.id = r.inventory_item_id
                 WHERE r.product_id = :product_id'
            );

            $recipeStmt->execute([
                ':product_id' => $productId,
            ]);

            $product['recipeItems'] = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);

            $result[] = toProductDto($product);
        }

        $pdo->commit();

        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
