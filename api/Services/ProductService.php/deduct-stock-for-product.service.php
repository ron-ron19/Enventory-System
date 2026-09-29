<?php

declare(strict_types=1);

function deductStockForProduct(PDO $pdo, int $id, array $data): array
{
    $productQuantity = $data['quantity'];
    $recipeItems = $data['recipeItems'];

    $stmt = $pdo->prepare(
        'SELECT
            p.*,
            r.id AS recipe_item_id,
            r.inventory_item_id,
            r.quantity AS recipe_quantity,
            i.id AS inventory_id,
            i.name AS inventory_name,
            i.quantity AS inventory_quantity
         FROM products p
         LEFT JOIN recipe_items r
            ON r.product_id = p.id
         LEFT JOIN inventory_items i
            ON i.id = r.inventory_item_id
         WHERE p.id = :id'
    );

    $stmt->execute([':id' => $id]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($rows) === 0) {
        throw new AppError(
            'NOT_FOUND',
            'Product not found'
        );
    }

    $product = $rows[0];
    $inventoryItems = [];

    foreach ($rows as $row) {
        if ($row['inventory_id'] !== null) {
            $inventoryItems[$row['inventory_id']] = [
                'id' => $row['inventory_id'],
                'name' => $row['inventory_name'],
                'quantity' => $row['inventory_quantity'],
            ];
        }
    }

    $insufficientStocks = [];

    foreach ($recipeItems as $recipeItem) {
        $inventoryItemId = $recipeItem['inventoryItemId'];
        $recipeQuantity = $recipeItem['quantity'];

        $inventoryItem = $inventoryItems[$inventoryItemId] ?? null;

        $requiredQuantity = $recipeQuantity * $productQuantity;
        $stockQuantity = $inventoryItem['quantity'] ?? 0;

        if ($requiredQuantity > $stockQuantity) {
            $insufficientStocks[] = [
                'name' => $inventoryItem['name'] ?? 'Unkown',
                'required' => $recipeQuantity,
                'available' => $stockQuantity,
            ];
        }
    }

    if (count($insufficientStocks) > 0) {
        throw new AppError(
            'STOCK_INSUFFICIENT',
            'Stock insufficient',
            $insufficientStocks
        );
    }

    try {
        $pdo->beginTransaction();

        $transactionStmt = $pdo->prepare(
            'INSERT INTO transactions
                (quantity, unit_price, transaction_price, product_name)
             VALUES
                (:quantity, :unit_price, :transaction_price, :product_name)'
        );

        $transactionStmt->execute([
            ':quantity' => $productQuantity,
            ':unit_price' => $product['price'],
            ':transaction_price' => $product['price'] * $productQuantity,
            ':product_name' => $product['name'],
        ]);

        $transactionId = (int) $pdo->lastInsertId();

        $logStmt = $pdo->prepare(
            'INSERT INTO inventory_logs
                (quantity_change, reason, inventory_item_id, source_type, source_id, item_name)
             VALUES
                (:quantity_change, :reason, :inventory_item_id, :source_type, :source_id, :item_name)'
        );

        $updateStmt = $pdo->prepare(
            'UPDATE inventory_items
             SET quantity = quantity - :quantity
             WHERE id = :id'
        );

        $updatedItems = [];

        foreach ($recipeItems as $recipeItem) {
            $inventoryItemId = $recipeItem['inventoryItemId'];
            $recipeQuantity = $recipeItem['quantity'];
            $totalDeduction = $recipeQuantity * $productQuantity;

            $logStmt->execute([
                ':quantity_change' => -abs($totalDeduction),
                ':reason' => 'Production/Sale of ' . $product['name'],
                ':inventory_item_id' => $inventoryItemId,
                ':source_type' => 'TRANSACTION',
                ':source_id' => $transactionId,
                ':item_name' => $inventoryItems[$inventoryItemId]['name'] ?? 'Unkown',
            ]);

            $updateStmt->execute([
                ':quantity' => $totalDeduction,
                ':id' => $inventoryItemId,
            ]);

            $itemStmt = $pdo->prepare(
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

            $itemStmt->execute([
                ':id' => $inventoryItemId,
            ]);

            $updatedItems[] = $itemStmt->fetch(PDO::FETCH_ASSOC);
        }

        $pdo->commit();

        return array_map(
            'toInventoryItemDto',
            $updatedItems
        );
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
