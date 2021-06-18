<?php

namespace Riddlestone\Brokkr\Users\Mvc\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Riddlestone\Brokkr\Users\Repository\UserRepository;

class UsersControllerFactory extends AbstractActionControllerFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = parent::__invoke($container, $requestedName, $options);

        if (!$controller instanceof UsersController) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is not an instance of %s',
                $requestedName,
                UsersController::class
            ));
        }

        $controller->setUserRepository($container->get(UserRepository::class));

        return $controller;
    }
}
