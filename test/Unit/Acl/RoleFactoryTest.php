<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Unit\Acl;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use PHPUnit\Framework\TestCase;
use Riddlestone\Brokkr\Users\Acl\RoleFactory;
use Riddlestone\Brokkr\Users\Entity\User;
use Riddlestone\Brokkr\Users\Repository\UserRepository;
use stdClass;

class RoleFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->createPartialMock(User::class, ['getId']);
        $user->method('getId')->willReturn('test-user-id');

        $userRepo = $this->createMock(UserRepository::class);
        $userRepo->method('find')->willReturnCallback(
            function ($id) use ($user) {
                if ($id == 'test-user-id') {
                    return $user;
                }
                return null;
            }
        );

        $this->container = $this->createMock(ContainerInterface::class);
        $this->container->method('get')->willReturnCallback(
            function ($name) use ($userRepo) {
                switch ($name) {
                    case UserRepository::class:
                        return $userRepo;
                }
                throw new ServiceNotFoundException();
            }
        );
    }

    public function testCanCreate()
    {
        $factory = new RoleFactory();
        $this->assertFalse($factory->canCreate($this->container, 'stdClass'));
        $this->assertFalse($factory->canCreate($this->container, stdClass::class . ':test-user-id'));
        $this->assertTrue($factory->canCreate($this->container, User::class . ':test-user-id'));
        $this->assertTrue($factory->canCreate($this->container, User::class . ':missing-user'));
    }

    /**
     * @throws ContainerException
     */
    public function testInvoke()
    {
        $factory = new RoleFactory();
        /** @var User $user */
        $user = $factory($this->container, User::class . ':test-user-id');
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(User::class . ':test-user-id', $user->getRoleId());
    }

    /**
     * @throws ContainerException
     */
    public function testInvalidUserInvoke()
    {
        $this->expectException(ServiceNotFoundException::class);

        $factory = new RoleFactory();
        $factory($this->container, User::class . ':missing-user');
    }

    /**
     * @throws ContainerException
     */
    public function testInvalidInvoke()
    {
        $this->expectException(ServiceNotCreatedException::class);

        $factory = new RoleFactory();
        $factory($this->container, stdClass::class);
    }
}
