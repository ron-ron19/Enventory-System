<?php

function stockIn(string $id): void
{
    // Equivalent of req.body
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    // Equivalent of:
    // inventoryItemsService.stockIn(req.params.id, req.body)
    $result = inventoryItemsServiceStockIn(
        $id,
        $body
    );

    // Equivalent of:
    // res.status(200).json(...)
    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Stock updated successfully',
        'data' => $result,
    ]);
}
