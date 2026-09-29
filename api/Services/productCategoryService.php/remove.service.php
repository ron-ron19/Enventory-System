<?php

declare(strict_types=1);

function remove(PDO $pdo, int $id): array
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
            'Category not found'
        );
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'UPDATE products
             SET category_id = NULL
             WHERE category_id = :category_id'
        );

        $stmt->execute([
            ':category_id' => $id,
        ]);

        $stmt = $pdo->prepare(
            'DELETE FROM product_categories
             WHERE id = :id'
        );

        $stmt->execute([
            ':id' => $id,
        ]);

        $pdo->commit();

        return [
            'createdAt' => $category['created_at'],
            'updatedAt' => $category['updated_at'],
            ...toProductCategoryDto($category),
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
