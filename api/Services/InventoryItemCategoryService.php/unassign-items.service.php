<?php

declare(strict_types=1);

function unassignItems(PDO $pdo, int $id, array $data): array
{
    $inventoryItemIds = $data['inventoryItemIds'];

    $stmt = $pdo->prepare(
        'SELECT id
         FROM inventory_item_categories
         WHERE id = :id
         LIMIT 1'
    );

    $stmt->execute([':id' => $id]);

    if ($stmt->fetch(PDO::FETCH_ASSOC) === false) {
        throw new AppError(
            'NOT_FOUND',
            'Item category not found'
        );
    }

    try {
        $pdo->beginTransaction();

        $updateStmt = $pdo->prepare(
            'UPDATE inventory_items
             SET category_id = NULL
             WHERE id = :item_id'
        );

        $selectStmt = $pdo->prepare(
            'SELECT
                i.*,
                c.id AS category_id,
                c.name AS category_name
             FROM inventory_items i
             LEFT JOIN inventory_item_categories c
                ON c.id = i.category_id
             WHERE i.id = :item_id
             LIMIT 1'
        );

        $result = [];

        foreach ($inventoryItemIds as $itemId) {
            $updateStmt->execute([
                ':item_id' => $itemId,
            ]);

            $selectStmt->execute([
                ':item_id' => $itemId,
            ]);

            $item = $selectStmt->fetch(PDO::FETCH_ASSOC);

            if ($item === false) {
                throw new AppError(
                    'NOT_FOUND',
                    'Inventory item not found'
                );
            }

            $result[] = toInventoryItemDto($item);
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
