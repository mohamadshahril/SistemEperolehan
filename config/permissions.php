<?php

return [
    // Static catalog of application permissions grouped by module/area
    'catalog' => [
        'Settings' => [
            ['name' => 'view users', 'description' => 'View users list and details'],
            ['name' => 'manage users', 'description' => 'Create, update, and assign roles/permissions to users'],
            ['name' => 'view roles', 'description' => 'View roles list and details'],
            ['name' => 'manage roles', 'description' => 'Create, update and delete roles'],
            ['name' => 'view permissions', 'description' => 'View permissions list and details'],
            ['name' => 'manage permissions', 'description' => 'Create, update and delete permissions'],
        ],
        'Purchase Requests' => [
            ['name' => 'create purchase requests', 'description' => 'Create and submit purchase requests'],
            ['name' => 'view approvals', 'description' => 'View purchase request approvals list and details'],
            ['name' => 'approve purchase requests', 'description' => 'Approve purchase requests'],
            ['name' => 'reject purchase requests', 'description' => 'Reject purchase requests'],
        ],
        'Vendors' => [
            ['name' => 'view vendors', 'description' => 'View vendors list and details'],
            ['name' => 'manage vendors', 'description' => 'Create, update and delete vendors'],
        ],
        'Purchase Orders' => [
            ['name' => 'view purchase orders', 'description' => 'View purchase orders list and details'],
            ['name' => 'manage purchase orders', 'description' => 'Create, update and delete purchase orders'],
            ['name' => 'print purchase orders', 'description' => 'Print purchase orders'],
        ],
        'Delivery Orders' => [
            ['name' => 'view delivery orders', 'description' => 'View delivery orders list and details'],
            ['name' => 'manage delivery orders', 'description' => 'Create, update and delete delivery orders'],
            ['name' => 'confirm delivery orders', 'description' => 'Confirm receipt for delivery orders'],
            ['name' => 'print delivery orders', 'description' => 'Print delivery orders summary'],
        ],
        'Delivery Reports' => [
            ['name' => 'view delivery reports', 'description' => 'View delivery reports list and details'],
            ['name' => 'export delivery reports', 'description' => 'Export delivery reports to PDF/CSV'],
        ],
        'Locations' => [
            ['name' => 'view locations', 'description' => 'View locations list and details'],
            ['name' => 'manage locations', 'description' => 'Create, update and delete locations'],
        ],
        'Vots' => [
            ['name' => 'view vots', 'description' => 'View VOTs list and details'],
            ['name' => 'manage vots', 'description' => 'Create, update and delete VOTs'],
        ],
        'Type Procurements' => [
            ['name' => 'view type procurements', 'description' => 'View type procurements list and details'],
            ['name' => 'manage type procurements', 'description' => 'Create, update and delete type procurements'],
        ],
        'Item Units' => [
            ['name' => 'view item units', 'description' => 'View item units list and details'],
            ['name' => 'manage item units', 'description' => 'Create, update and delete item units'],
        ],
        'Tenders' => [
            ['name' => 'view tenders', 'description' => 'View tenders list and details'],
            ['name' => 'manage tenders', 'description' => 'Create, update and delete tenders'],
            ['name' => 'award tenders', 'description' => 'Award tenders to bidders'],
        ],
        'Tender Bids' => [
            ['name' => 'view tender bids', 'description' => 'View tender bids list and details'],
            ['name' => 'manage tender bids', 'description' => 'Create, update and delete tender bids'],
        ],
    ],
];
