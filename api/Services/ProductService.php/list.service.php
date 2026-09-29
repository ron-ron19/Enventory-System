<?php

declare(strict_types=1);

function listProducts(PDO $pdo, array $query = []): array
{
    $filter = $query['filter'] ?? [];
    $options = $query['options'] ?? [];

    $name = $filter['name'] ?? null;
    $description = $filter['description'] ?? null;
    $categoryId = $filter['categoryId'] ?? null;
    $price = $filter['price'] ?? null;

    $order = strtolower($options['order'] ?? 'asc') === 'desc'
        ? 'DESC'
        : 'ASC';

    $sortBy = $options['sortBy'] ?? null;
    $lastProductId = $options['lastProductId'] ?? null;

    $allowedSortColumns = [
        'id',
        'name',
        'description',
        'price',
        'category_id',
        'created_at',
        'updated_at',
    ];

    if (!in_array($sortBy, $allowedSortColumns, true)) {
        $sortBy = 'id';
    }

    $where = [];
    $params = [];

    if ($name !== null) {
        $where[] = 'LOWER(p.name) LIKE LOWER(:name)';
        $params[':name'] = '%' . $name . '%';
    }

    if ($description !== null) {
        $where[] = 'LOWER(p.description) LIKE LOWER(:description)';
        $params[':description'] = '%' . $description . '%';
    }

    if ($categoryId !== null) {
        $categoryIds = is_array($categoryId)
            ? $categoryId
            : [$categoryId];

        $placeholders = [];

        foreach ($categoryIds as $index => $value) {
            $placeholder = ':category_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $value;
        }

        $where[] = 'p.category_id IN (' . implode(', ', $placeholders) . ')';
    }

    if ($price !== null) {
        if (is_array($price)) {
            if (isset($price['gte'])) {
                $where[] = 'p.price >= :price_gte';
                $params[':price_gte'] = $price['gte'];
            }

            if (isset($price['lte'])) {
                $where[] = 'p.price <= :price_lte';
                $params[':price_lte'] = $price['lte'];
            }

            if (isset($price['gt'])) {
                $where[] = 'p.price > :price_gt';
                $params[':price_gt'] = $price['gt'];
            }

            if (isset($price['lt'])) {
                $where[] = 'p.price < :price_lt';
                $params[':price_lt'] = $price['lt'];
            }

            if (isset($price['equals'])) {
                $where[] = 'p.price = :price_equals';
                $params[':price_equals'] = $price['equals'];
            }
        } else {
            $where[] = 'p.price = :price';
            $params[':price'] = $price;
        }
    }

    if ($lastProductId !== null) {
        $where[] = 'p.id >= :last_product_id';
        $params[':last_product_id'] = $lastProductId;
    }

    $sql = '
        SELECT
            p.*,
            c.id AS category_id,
            c.name AS category_name,
            r.id AS recipe_item_id,
            r.quantity AS recipe_quantity,
            i.id AS inventory_item_id,
            i.name AS inventory_item_name,
            i.quantity AS inventory_item_quantity,
            ic.id AS inventory_category_id,
            ic.name AS inventory_category_name
        FROM products p
        LEFT JOIN product_categories c
            ON c.id = p.category_id
        LEFT JOIN recipe_items r
            ON r.product_id = p.id
        LEFT JOIN inventory_items i
            ON i.id = r.inventory_item_id
        LEFT JOIN inventory_item_categories ic
            ON ic.id = i.category_id
    ';

    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= " ORDER BY p.$sortBy $order";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $products = [];

    foreach ($rows as $row) {
        $productId = $row['id'];

        if (!isset($products[$productId])) {
            $products[$productId] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'imageUrl' => $row['image_url'],
                'price' => $row['price'],
                'category' => $row['category_id'] !== null
                    ? [
                        'id' => $row['category_id'],
                        'name' => $row['category_name'],
                    ]
                    : null,
                'recipeItems' => [],
            ];
        }

        if ($row['recipe_item_id'] !== null) {
            $products[$productId]['recipeItems'][] = [
                'id' => $row['recipe_item_id'],
                'quantity' => $row['recipe_quantity'],
                'inventoryItem' => [
                    'id' => $row['inventory_item_id'],
                    'name' => $row['inventory_item_name'],
                    'quantity' => $row['inventory_item_quantity'],
                    'category' => $row['inventory_category_id'] !== null
                        ? [
                            'id' => $row['inventory_category_id'],
                            'name' => $row['inventory_category_name'],
                        ]
                        : null,
                ],
            ];
        }
    }

    return array_map(
        'toProductWithInventoryItemsDto',
        array_values($products)
    );
}
