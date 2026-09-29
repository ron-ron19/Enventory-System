<?php

function create(): void
{
    // Equivalent of req.body
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    // Equivalent of:
    // inventoryItemCategoriesService.create(req.body)
    $result = inventoryItemCategoriesServiceCreate($body);

    // Equivalent of:
    // res.status(201).json(...)
    http_response_code(201);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Category successfully created',
        'data' => $result,
    ]);
}
