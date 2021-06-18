<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Unit\Controller;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\Authentication\AuthenticationService;
use PHPUnit\Framework\TestCase;
use Riddlestone\Brokkr\Acl\Acl;
use Riddlestone\Brokkr\Users\Mvc\Controller\UsersController;
use Riddlestone\Brokkr\Users\Mvc\Controller\UsersControllerFactory;
use Riddlestone\Brokkr\Users\Repository\UserRepository;

class UsersControllerFactoryTest extends TestCase
{
    /**
     * @throws ContainerException
     */
    public function testInvoke()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->method('get')
            ->willReturnMap([
                [Acl::class, $this->createMock(Acl::class)],
                [AuthenticationService::class, $this->createMock(AuthenticationService::class)],
                [UserRepository::class, $this->createMock(UserRepository::class)],
            ]);

        $factory = new UsersControllerFactory();

        $controller = $factory($container, UsersController::class);

        $this->assertInstanceOf(UsersController::class, $controller);
    }
}
