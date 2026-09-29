<?php

function getById(string $id): void
{
    $result = inventoryItemsServiceGetById($id);

    http_response_code(200);
    header('Content-Type: application/json');

    echo json_encode([
        'data' => $result,
        'message' => 'Item successfully retrieved',
    ]);
}