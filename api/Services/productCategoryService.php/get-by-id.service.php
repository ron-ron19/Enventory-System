<?php

declare(strict_types=1);

function getById(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare(
        'SELECT *
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

    $stmt = $pdo->prepare(
        'SELECT *
         FROM products
         WHERE category_id = :category_id'
    );

    $stmt->execute([
        ':category_id' => $id,
    ]);

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$product) {
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
            ':product_id' => $product['id'],
        ]);

        $product['recipeItems'] = $recipeStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $category['products'] = $products;

    return toProductCategoryWithProductsDto($category);
}
