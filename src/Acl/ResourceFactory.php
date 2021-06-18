<?php

namespace Riddlestone\Brokkr\Users\Mvc\Acl;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Riddlestone\Brokkr\Acl\GenericResource;
use Riddlestone\Brokkr\Users\Mvc\Controller\AccountController;
use Riddlestone\Brokkr\Users\Mvc\Controller\UsersController;

class ResourceFactory implements AbstractFactoryInterface
{
    protected $actions = [
        AccountController::class => ['loginAction', 'logoutAction'],
        UsersController::class => ['indexAction'],
    ];

    /**
     * @inheritDoc
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (strpos($requestedName, '::') === false) {
            return array_key_exists($requestedName, $this->actions);
        }

        list($controller, $action) = explode('::', $requestedName);

        return isset($this->actions[$controller])
            && in_array($action, $this->actions[$controller]);
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (strpos($requestedName, '::') === false) {
            return new GenericResource($requestedName);
        }

        list($controller) = explode('::', $requestedName);

        return new GenericResource($requestedName, $controller);
    }
}
