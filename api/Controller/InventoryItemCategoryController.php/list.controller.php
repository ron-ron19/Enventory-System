<?php

function listCategories(): void
{
    // Equivalent of req.query
    $query = $_GET;

    // Equivalent of validateSchema(...)
    $query = validateInventoryItemCategoriesQuery($query);

    // Equivalent of:
    // inventoryItemCategoriesService.list(query)
    $result = inventoryItemCategoriesServiceList($query);

    // Equivalent of:
    // res.status(200).json(...)
    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'success',
        'data' => $result,
    ]);
}

function validateInventoryItemCategoriesQuery(array $query): array
{
    // Convert getInventoryItemCategoriesReqQuerySchema
    // validation here.

    return $query;
}
