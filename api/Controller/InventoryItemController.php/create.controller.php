<?php

function create(): void
{
    // Equivalent of req.body
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    // Equivalent of:
    // inventoryItemsService.create(req.body)
    $result = inventoryItemsServiceCreate($body);

    // Equivalent of:
    // res.status(201).json(...)
    http_response_code(201);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Item successfully created',
        'data' => $result,
    ]);
}
