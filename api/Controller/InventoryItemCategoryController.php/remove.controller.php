<?php

function remove(string $id): void
{
    // Equivalent of:
    // inventoryItemCategoriesService.delete(req.params.id)
    $result = inventoryItemCategoriesServiceDelete($id);

    // Equivalent of:
    // res.status(200).json(...)
    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Item category successfully deleted',
        'data' => $result,
    ]);
}
