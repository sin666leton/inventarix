<?php

return [
    'admin' => [
        "*"
    ],

    'staff' => [
        'viewAny:category',
        'viewAny:item',
        'viewAny:transaction',

        'view:category',
        'view:item',
        'view:transaction',

        'create:transaction',
    ]
];