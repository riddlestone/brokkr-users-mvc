<?php

namespace Riddlestone\Brokkr\Users\Mvc\Controller;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Riddlestone\Brokkr\Acl\Acl;

class AbstractActionControllerFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = new $requestedName();

        if (!$controller instanceof AbstractActionController) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is not an instance of %s',
                $requestedName,
                AbstractActionController::class
            ));
        }

        $controller->setAcl($container->get(Acl::class));
        $controller->setAuthenticationService($container->get(AuthenticationService::class));

        return $controller;
    }
}
