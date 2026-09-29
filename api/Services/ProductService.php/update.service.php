<?php

declare(strict_types=1);

function update(PDO $pdo, int $id, array $data): array
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

    $categoryId = $data['categoryId'] ?? null;

    if ($categoryId !== null) {
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

        $fields = [];
        $params = [':id' => $id];

        if (array_key_exists('categoryId', $data)) {
            $fields[] = 'category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        if (array_key_exists('description', $data)) {
            $fields[] = 'description = :description';
            $params[':description'] = $data['description'];
        }

        if (array_key_exists('name', $data)) {
            $fields[] = 'name = :name';
            $params[':name'] = $data['name'];
        }

        if (array_key_exists('price', $data)) {
            $fields[] = 'price = :price';
            $params[':price'] = $data['price'];
        }

        if (array_key_exists('imageUrl', $data)) {
            $fields[] = 'image_url = :image_url';
            $params[':image_url'] = $data['imageUrl'];
        }

        if ($fields) {
            $sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = :id';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        if (array_key_exists('recipeItems', $data)) {
            $stmt = $pdo->prepare(
                'DELETE FROM recipe_items
                 WHERE product_id = :product_id'
            );

            $stmt->execute([
                ':product_id' => $id,
            ]);

            if (count($data['recipeItems']) >= 1) {
                $stmt = $pdo->prepare(
                    'INSERT INTO recipe_items
                        (product_id, inventory_item_id, quantity)
                     VALUES
                        (:product_id, :inventory_item_id, :quantity)'
                );

                foreach ($data['recipeItems'] as $recipeItem) {
                    $stmt->execute([
                        ':product_id' => $id,
                        ':inventory_item_id' => $recipeItem['inventoryItemId'],
                        ':quantity' => $recipeItem['quantity'],
                    ]);
                }
            }
        }

        $stmt = $pdo->prepare(
            'SELECT
                p.*,
                c.id AS category_id,
                c.name AS category_name,
                r.id AS recipe_item_id,
                r.quantity AS recipe_quantity,
                i.id AS inventory_item_id,
                i.name AS inventory_item_name,
                i.quantity AS inventory_item_quantity
             FROM products p
             LEFT JOIN product_categories c
                ON c.id = p.category_id
             LEFT JOIN recipe_items r
                ON r.product_id = p.id
             LEFT JOIN inventory_items i
                ON i.id = r.inventory_item_id
             WHERE p.id = :id'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $product = $rows[0];

        $product['category'] = $product['category_id'] !== null
            ? [
                'id' => $product['category_id'],
                'name' => $product['category_name'],
            ]
            : null;

        $product['recipeItems'] = [];

        foreach ($rows as $row) {
            if ($row['recipe_item_id'] !== null) {
                $product['recipeItems'][] = [
                    'id' => $row['recipe_item_id'],
                    'quantity' => $row['recipe_quantity'],
                    'inventoryItem' => [
                        'id' => $row['inventory_item_id'],
                        'name' => $row['inventory_item_name'],
                        'quantity' => $row['inventory_item_quantity'],
                    ],
                ];
            }
        }

        $pdo->commit();

        return toProductWithInventoryItemsDto($product);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
    