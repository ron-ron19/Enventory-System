<?php

function remove(string $id): void
{
    $result = productsServiceDelete($id);

    http_response_code(200);

    header('Content-Type: application/json');

    echo json_encode([
        'message' => 'Product successfully deleted',
        'data' => $result,
    ]);
}
