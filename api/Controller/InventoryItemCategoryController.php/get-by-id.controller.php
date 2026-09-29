<?php

function getById(string $id): void
{
    // Equivalent of:
    // inventoryItemCategoriesService.getById(req.params.id)
    $result = inventoryItemCategoriesServiceGetById($id);

    // Equivalent of:
    // res.status(200).json(...)
    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'success',
        'data' => $result,
    ]);
}
