<?php

namespace Riddlestone\Brokkr\Users\Mvc;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'routes' => [
        'brokkr-users:account' => [
            'type' => Literal::class,
            'options' => [
                'route' => '/account',
                'defaults' => [
                    'controller' => Controller\AccountController::class,
                    'action' => 'index',
                ],
            ],
        ],
        'brokkr-users:request-password-reset' => [
            'type' => Literal::class,
            'options' => [
                'route' => '/request-password-reset',
                'defaults' => [
                    'controller' => Controller\AccountController::class,
                    'action' => 'requestPasswordReset',
                ],
            ],
        ],
        'brokkr-users:reset-password' => [
            'type' => Segment::class,
            'options' => [
                'route' => '/reset-password/:id',
                'defaults' => [
                    'controller' => Controller\AccountController::class,
                    'action' => 'resetPassword',
                ],
            ],
        ],
        'brokkr-users:login' => [
            'type' => Literal::class,
            'options' => [
                'route' => '/login',
                'defaults' => [
                    'controller' => Controller\AccountController::class,
                    'action' => 'login',
                ],
            ],
        ],
        'brokkr-users:logout' => [
            'type' => Literal::class,
            'options' => [
                'route' => '/logout',
                'defaults' => [
                    'controller' => Controller\AccountController::class,
                    'action' => 'logout',
                ],
            ],
        ],
    ],
];
