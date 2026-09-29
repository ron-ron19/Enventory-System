<?php

require_once __DIR__ . '/create.controller.php';
require_once __DIR__ . '/get-by-id.controller.php';
require_once __DIR__ . '/list.controller.php';
require_once __DIR__ . '/remove.controller.php';
require_once __DIR__ . '/stock-in.controller.php';
require_once __DIR__ . '/stock-out.controller.php';
require_once __DIR__ . '/update.controller.php';

$inventoryItemsController = [
    'create' => 'create',
    'update' => 'update',
    'delete' => 'remove',
    'list' => 'list',
    'getById' => 'getById',
    'stockIn' => 'stockIn',
    'stockOut' => 'stockOut',
];
