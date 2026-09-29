<?php

function getAll(): void
{
    // Equivalent of req.query
    $query = $_GET;

    // Equivalent of:
    // validateSchema(getProductCategoriesReqQuerySchema, req.query)
    $query = validateProductCategoriesQuery($query);

    // Equivalent of:
    // productCategoriesService.list(query)
    $result = productCategoriesServiceList($query);

    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'success',
        'data' => $result,
    ]);
}

function validateProductCategoriesQuery(array $query): array
{
    // Put the equivalent schema validation here.
    return $query;
}
