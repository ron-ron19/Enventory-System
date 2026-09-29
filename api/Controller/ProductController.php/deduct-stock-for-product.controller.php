<?php

function deductStockForProduct(string $id): void
{
    $body = json_decode(
        file_get_contents('php://input'),
        true
    );

    $result = productsServiceDeductStockForProduct(
        $id,
        $body
    );

    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Stock successfully deducted for production',
        'data' => $result,
    ]);
}
