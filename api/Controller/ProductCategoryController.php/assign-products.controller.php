<?php

function update(string $id): void
{
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    $result = inventoryItemsServiceUpdate($id, $body);

    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Item updated successfully',
        'data' => $result,
    ]);
}
