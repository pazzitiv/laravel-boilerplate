<?php

return [
    'name' => 'DataSource',
    'driver' => env('DATASOURCE_DRIVER', 'Polymatica'),
    'moduleSystem' => [
        'module' => 'datasource',
        'parentModule' => null,
    ]
];
