<?php

function updateProductCategory(string $id): void
{
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    $result = productCategoriesServiceUpdate(
        $id,
        $body
    );

    http_response_code(200);
    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Product category updated successfully',
        'data' => $result,
    ]);
}