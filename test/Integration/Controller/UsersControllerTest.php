<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Integration\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Laminas\View\Model\ViewModel;
use Riddlestone\Brokkr\Acl\Acl;
use Riddlestone\Brokkr\Acl\GenericRule;
use Riddlestone\Brokkr\Users\Mvc\Controller\UsersController;
use Riddlestone\Brokkr\Users\Entity\User;
use Riddlestone\Brokkr\Users\Mvc\Test\Integration\AbstractApplicationTestCase;

class UsersControllerTest extends AbstractApplicationTestCase
{
    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testIndexAction()
    {
        /** @var EntityManager $em */
        $em = $this->app->getServiceManager()->get(EntityManager::class);
        for ($i = 1; $i <= 125; $i++) {
            $user = new User();
            $user->setFirstName('Test User');
            $user->setLastName('#' . $i);
            $user->setEmailAddress('test-' . $i . '@example.com');
            $user->setPassword('password', $this->app->getServiceManager()->get('Config')['global_salt']);
            $em->persist($user);
        }
        $em->flush();

        /** @var Acl $acl */
        $acl = $this->app->getServiceManager()->get(Acl::class);
        $acl->addRule(new GenericRule(\Laminas\Permissions\Acl\Acl::TYPE_ALLOW, null, null));

        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(UsersController::class, 'index');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/users/index', $viewModel->getTemplate());
        $this->assertEquals(3, $viewModel->getVariable('pages'));
        $this->assertEquals(1, $viewModel->getVariable('page'));
        $this->assertCount(50, $viewModel->getVariable('users'));

        $this->app->getRequest()->getQuery()->set('page', '2');

        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(UsersController::class, 'index');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/users/index', $viewModel->getTemplate());
        $this->assertEquals(3, $viewModel->getVariable('pages'));
        $this->assertEquals(2, $viewModel->getVariable('page'));
        $this->assertCount(50, $viewModel->getVariable('users'));

        $this->app->getRequest()->getQuery()->set('page', '3');

        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(UsersController::class, 'index');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/users/index', $viewModel->getTemplate());
        $this->assertEquals(3, $viewModel->getVariable('pages'));
        $this->assertEquals(3, $viewModel->getVariable('page'));
        $this->assertCount(25, $viewModel->getVariable('users'));
    }
}
