<?php

function remove(string $id): void
{
    // Equivalent of:
    // inventoryItemsService.delete(req.params.id)
    $result = inventoryItemsServiceDelete($id);

    // Equivalent of:
    // res.status(200).json(...)
    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Item successfully deleted',
        'data' => $result,
    ]);
}
