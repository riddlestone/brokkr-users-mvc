<?php

namespace Riddlestone\Brokkr\Users\Mvc\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Riddlestone\Brokkr\Users\Repository\UserRepository;
use Riddlestone\Brokkr\Users\Service\PasswordResetService;

class AccountControllerFactory extends AbstractActionControllerFactory
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = parent::__invoke($container, $requestedName, $options);

        if (!$controller instanceof AccountController) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is not an instance of %s',
                $requestedName,
                AccountController::class
            ));
        }

        $controller->setUserRepository($container->get(UserRepository::class));
        $controller->setFormElementManager($container->get('FormElementManager'));
        $controller->setPasswordResetService($container->get(PasswordResetService::class));

        return $controller;
    }
}
