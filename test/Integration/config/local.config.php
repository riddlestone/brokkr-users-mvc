<?php

use Doctrine\DBAL\Driver\PDOSqlite\Driver;
use Laminas\Router\Http\Literal;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => Driver::class,
                'params' => [
                    'memory' => true,
                ],
            ],
        ],
    ],
    'mail' => [
        'transport' => [
            'type' => 'inmemory',
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'name' => 'home',
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../views',
        ],
    ],
];
