<?php

function getAll(): void
{
    // Equivalent of req.query
    $query = $_GET;

    // Equivalent of:
    // validateSchema(getInventoryItemsReqQuerySchema, req.query)
    $query = validateInventoryItemsQuery($query);

    // Equivalent of:
    // inventoryItemsService.list(query.filter, query.options)
    $result = inventoryItemsServiceList(
        $query['filter'],
        $query['options']
    );

    // Equivalent of:
    // res.status(200).json(...)
    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'success',
        'data' => $result,
    ]);
}

function validateInventoryItemsQuery(array $query): array
{
    // Convert getInventoryItemsReqQuerySchema validation here.
    return $query;
}
