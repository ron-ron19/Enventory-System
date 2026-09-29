<?php

function getAll(): void
{
    // Equivalent of req.query
    $query = $_GET;

    // Equivalent of:
    // validateSchema(getProductsReqQuerySchema, req.query)
    $filter = validateProductsQuery($query);

    // Equivalent of:
    // productsService.list(filter)
    $result = productsServiceList($filter);

    // Equivalent of:
    // res.status(200).json(...)
    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'success',
        'data' => $result,
    ]);
}

function validateProductsQuery(array $query): array
{
    // Convert getProductsReqQuerySchema validation here.
    return $query;
}
