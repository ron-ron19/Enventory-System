<?php

require_once __DIR__ . '/assign-items.controller.php';
require_once __DIR__ . '/create.controller.php';
require_once __DIR__ . '/get-by-id.controller.php';
require_once __DIR__ . '/list.controller.php';
require_once __DIR__ . '/remove.controller.php';
require_once __DIR__ . '/unassign-items.controller.php';
require_once __DIR__ . '/update.controller.php';

$inventoryItemCategoriesController = [
    'create' => 'create',
    'list' => 'list',
    'getById' => 'getById',
    'delete' => 'remove',
    'update' => 'update',
    'assignItems' => 'assignItems',
    'unassignItems' => 'unassignItems',
];
