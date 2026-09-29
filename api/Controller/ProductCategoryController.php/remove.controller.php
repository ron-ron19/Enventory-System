<?php

function remove(string $id): void
{
    $result = productCategoriesServiceDelete($id);

    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Product category deleted successfully',
        'data' => $result,
    ]);
}
