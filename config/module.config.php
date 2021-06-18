<?php

namespace Riddlestone\Brokkr\Users\Mvc;

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'acl_resource_manager' => [
        'abstract_factories' => [
            Acl\ResourceFactory::class,
        ],
    ],
    'acl_rule_manager' => [
        'factories' => [
            Acl\RuleProvider::class => InvokableFactory::class,
        ],
        'providers' => [
            Acl\RuleProvider::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AccountController::class => Controller\AccountControllerFactory::class,
            Controller\UsersController::class => Controller\UsersControllerFactory::class,
        ],
    ],
    'navigation' => [
        'admin' => [
            'admin' => [
                'pages' => [
                    'users' => [
                        'label' => 'User Management',
                        'route' => 'admin/users',
                        'resource' => Controller\UsersController::class . '::indexAction',
                    ],
                ],
            ],
        ],
        'personal' => [
            'admin' => [
                'pages' => [
                    'users' => [
                        'label' => 'User Management',
                        'route' => 'admin/users',
                        'resource' => Controller\UsersController::class . '::indexAction',
                    ],
                ],
            ],
            'login' => [
                'label' => 'Login',
                'route' => 'brokkr-users:login',
                'resource' => Controller\AccountController::class . '::loginAction',
                'class' => 'hollow button',
            ],
            'logout' => [
                'label' => 'Logout',
                'route' => 'brokkr-users:logout',
                'resource' => Controller\AccountController::class . '::logoutAction',
                'class' => 'hollow button',
            ],
        ],
    ],
    'router' => require __DIR__ . '/module.routes.php',
];
