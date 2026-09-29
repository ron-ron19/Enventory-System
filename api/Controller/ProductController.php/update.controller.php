<?php

function update(string $id): void
{
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    $result = productsServiceUpdate(
        $id,
        $body
    );

    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Product successfully updated',
        'data' => $result,
    ]);
}
