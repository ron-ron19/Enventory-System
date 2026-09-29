<?php

function create(): void
{
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    $result = productsServiceCreate($body);

    http_response_code(201);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Item successfully created',
        'data' => $result,
    ]);
}
