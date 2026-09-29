<?php

function getById(string $id): void
{
    $result = productCategoriesServiceGetById($id);

    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'success',
        'data' => $result,
    ]);
}
