<?php

require_once __DIR__ . '/create.controller.php';
require_once __DIR__ . '/deduct-stock-for-product.controller.php';
require_once __DIR__ . '/get-by-id.controller.php';
require_once __DIR__ . '/list.controller.php';
require_once __DIR__ . '/remove.controller.php';
require_once __DIR__ . '/update.controller.php';

$productsController = [
    'create' => 'create',
    'list' => 'list',
    'delete' => 'remove',
    'update' => 'update',
    'deductStockForProduct' => 'deductStockForProduct',
    'getById' => 'getById',
];
