<?php

declare(strict_types=1);

function stockIn(PDO $pdo, int $id, array $data): array
{
    $quantity = $data['quantity'];

    $stmt = $pdo->prepare(
        'SELECT id, name
         FROM inventory_items
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([':id' => $id]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item === false) {
        throw new AppError(
            'NOT_FOUND',
            'Item not found'
        );
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'UPDATE inventory_items
             SET quantity = quantity + :quantity
             WHERE id = :id'
        );

        $stmt->execute([
            ':quantity' => $quantity,
            ':id' => $id,
        ]);

        $stmt = $pdo->prepare(
            'INSERT INTO inventory_logs
                (inventory_item_id, quantity_change, reason, item_name, source_type)
             VALUES
                (:inventory_item_id, :quantity_change, :reason, :item_name, :source_type)'
        );

        $stmt->execute([
            ':inventory_item_id' => $id,
            ':quantity_change' => $quantity,
            ':reason' => 'Stock in',
            ':item_name' => $item['name'],
            ':source_type' => 'ADJUSTMENT',
        ]);

        $stmt = $pdo->prepare(
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

        $stmt->execute([':id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $pdo->commit();

        return toInventoryItemDto($result);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
