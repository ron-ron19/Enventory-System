<?php

require_once __DIR__ . '/assign-products.controller.php';
require_once __DIR__ . '/create.controller.php';
require_once __DIR__ . '/get-by-id.controller.php';
require_once __DIR__ . '/list.controller.php';
require_once __DIR__ . '/remove.controller.php';
require_once __DIR__ . '/unassign-products.controller.php';
require_once __DIR__ . '/update.controller.php';

$productCategoriesController = [
    'create' => 'create',
    'update' => 'update',
    'delete' => 'remove',
    'getById' => 'getById',
    'list' => 'list',
    'unassignProducts' => 'unassignProducts',
    'assignProducts' => 'assignProducts',
];
