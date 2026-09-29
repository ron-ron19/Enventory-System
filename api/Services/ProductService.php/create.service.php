<?php

declare(strict_types=1);

function create(PDO $pdo, array $data): array
{
    $categoryId = $data['categoryId'] ?? null;
    $recipeItems = $data['recipeItems'] ?? [];

    if ($categoryId) {
        $stmt = $pdo->prepare(
            'SELECT id
             FROM product_categories
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $categoryId,
        ]);

        if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
            throw new AppError(
                'NOT_FOUND',
                'Product category not found'
            );
        }
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO products
                (name, category_id, description, image_url, price)
             VALUES
                (:name, :category_id, :description, :image_url, :price)'
        );

        $stmt->execute([
            ':name' => $data['name'],
            ':category_id' => $categoryId,
            ':description' => $data['description'] ?? null,
            ':image_url' => $data['imageUrl'] ?? null,
            ':price' => $data['price'],
        ]);

        $productId = (int) $pdo->lastInsertId();

        if (count($recipeItems) > 0) {
            $recipeStmt = $pdo->prepare(
                'INSERT INTO recipe_items
                    (product_id, inventory_item_id, quantity)
                 VALUES
                    (:product_id, :inventory_item_id, :quantity)'
            );

            foreach ($recipeItems as $recipeItem) {
                $recipeStmt->execute([
                    ':product_id' => $productId,
                    ':inventory_item_id' => $recipeItem['inventoryItemId'],
                    ':quantity' => $recipeItem['quantity'],
                ]);
            }
        }

        $stmt = $pdo->prepare(
            'SELECT *
             FROM products
             WHERE id = :id
             LIMIT 1'
        );

        $stmt->execute([
            ':id' => $productId,
        ]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $recipeStmt = $pdo->prepare(
            'SELECT
                r.*,
                i.*,
                c.id AS inventory_category_id,
                c.name AS inventory_category_name
             FROM recipe_items r
             LEFT JOIN inventory_items i
                ON i.id = r.inventory_item_id
             LEFT JOIN inventory_item_categories c
                ON c.id = i.category_id
             WHERE r.product_id = :product_id'
        );

        $recipeStmt->execute([
            ':product_id' => $productId,
        ]);

        $product['recipeItems'] = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo->commit();

        return toProductDto($product);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
