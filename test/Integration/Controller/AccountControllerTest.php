<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Integration\Controller;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\ToolsException;
use Laminas\Http\Response;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Model\ViewModel;
use Riddlestone\Brokkr\Users\Mvc\Controller\AccountController;
use Riddlestone\Brokkr\Users\Entity\PasswordReset;
use Riddlestone\Brokkr\Users\Entity\User;
use Riddlestone\Brokkr\Users\Mvc\Form\LoginForm;
use Riddlestone\Brokkr\Users\Mvc\Form\PasswordResetForm;
use Riddlestone\Brokkr\Users\Mvc\Form\RequestPasswordResetForm;
use Riddlestone\Brokkr\Users\Repository\PasswordResetRepository;
use Riddlestone\Brokkr\Users\Mvc\Test\Integration\AbstractApplicationTestCase;

class AccountControllerTest extends AbstractApplicationTestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var AccountController
     */
    protected $controller;

    /**
     * @throws ToolsException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var EntityManager $em */
        $em = $this->app->getServiceManager()->get(EntityManager::class);
        $this->user = new User();
        $this->user->setFirstName('Test');
        $this->user->setLastName('User');
        $this->user->setEmailAddress('test@example.com');
        $this->user->setPassword('password', $this->app->getServiceManager()->get('Config')['global_salt']);
        $em->persist($this->user);
        $em->flush();
    }

    public function testIndexAction()
    {
        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'index');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/account/index', $viewModel->getTemplate());
        $this->assertNull($viewModel->getVariable('user', 'NOPE'));

        $this->authenticate($this->user);

        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'index');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/account/index', $viewModel->getTemplate());
        $this->assertEquals($this->user, $viewModel->getVariable('user', 'NOPE'));
    }

    public function testGetLoginAction()
    {
        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'login');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/account/login', $viewModel->getTemplate());
        $this->assertInstanceOf(LoginForm::class, $viewModel->getVariable('form'));
        $this->assertFalse($this->getAuthenticationService()->hasIdentity());
    }

    public function testPostValidLoginAction()
    {
        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $post = $this->app->getMvcEvent()->getRequest()->getPost();
        $post->set('email_address', 'test@example.com');
        $post->set('password', 'password');

        $redirect = $this->dispatch(AccountController::class, 'login');
        $this->assertInstanceOf(Response::class, $redirect);
        $this->assertTrue($this->getAuthenticationService()->hasIdentity());
        $this->assertInstanceOf(User::class, $this->getAuthenticationService()->getIdentity());
        $this->assertEquals('test@example.com', $this->getAuthenticationService()->getIdentity()->getEmailAddress());
        $this->assertEquals(
            ['Login successful'],
            $this->getFlashMessages(FlashMessenger::NAMESPACE_SUCCESS)
        );
    }

    public function testPostInvalidFormLoginAction()
    {
        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $post = $this->app->getMvcEvent()->getRequest()->getPost();
        $post->set('email_address', 'email-address-missing-at');
        $post->set('password', 'password');

        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'login');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/account/login', $viewModel->getTemplate());
        $this->assertInstanceOf(LoginForm::class, $viewModel->getVariable('form'));
        $this->assertFalse($this->getAuthenticationService()->hasIdentity());
    }

    public function testPostUserNotFoundLoginAction()
    {
        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $post = $this->app->getMvcEvent()->getRequest()->getPost();
        $post->set('email_address', 'user-missing@example.com');
        $post->set('password', 'password');

        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'login');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('brokkr/users/account/login', $viewModel->getTemplate());
        $this->assertInstanceOf(LoginForm::class, $viewModel->getVariable('form'));
        $this->assertFalse($this->getAuthenticationService()->hasIdentity());
    }

    public function testLogoutAction()
    {
        $this->authenticate($this->user);
        $this->assertTrue($this->getAuthenticationService()->hasIdentity());
        $this->assertInstanceOf(User::class, $this->getAuthenticationService()->getIdentity());
        $redirect = $this->dispatch(AccountController::class, 'logout');
        $this->assertInstanceOf(Response::class, $redirect);
        $this->assertFalse($this->getAuthenticationService()->hasIdentity());
    }

    public function testGetRequestPasswordResetAction()
    {
        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'requestPasswordReset');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertInstanceOf(RequestPasswordResetForm::class, $viewModel->getVariable('form'));
        $this->assertEquals('brokkr/users/account/request_password_reset', $viewModel->getTemplate());
    }

    public function testPostInvalidRequestPasswordResetAction()
    {
        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $post = $this->app->getMvcEvent()->getRequest()->getPost();
        $post->set('email_address', 'invalid-email-address');
        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'requestPasswordReset');
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertInstanceOf(RequestPasswordResetForm::class, $viewModel->getVariable('form'));
        /** @var RequestPasswordResetForm $form */
        $form = $viewModel->getVariable('form');
        $this->assertEquals(['regexNotMatch'], array_keys($form->getMessages('email_address')));
        $this->assertEquals('brokkr/users/account/request_password_reset', $viewModel->getTemplate());
    }

    public function testPostValidRequestPasswordResetAction()
    {
        /** @var PasswordResetRepository $passwordResetRepo */
        $passwordResetRepo = $this->app->getServiceManager()->get(PasswordResetRepository::class);
        $this->assertNull($passwordResetRepo->findOneBy(['user' => $this->user]));

        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $post = $this->app->getMvcEvent()->getRequest()->getPost();
        $post->set('email_address', 'test@example.com');

        /** @var Response $redirect */
        $redirect = $this->dispatch(AccountController::class, 'requestPasswordReset');
        $this->assertInstanceOf(Response::class, $redirect);
        $this->assertEquals('/', $redirect->getHeaders()->get('Location')->getFieldValue());

        /** @var Message $message */
        $message = $this->app->getServiceManager()->get(TransportInterface::class)->getLastMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertTrue($message->getTo()->has('test@example.com'));

        $reset = $passwordResetRepo->findOneBy(['user' => $this->user]);
        $this->assertInstanceOf(PasswordReset::class, $reset);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testGetResetPasswordAction()
    {
        /** @var EntityManager $em */
        $em = $this->app->getServiceManager()->get(EntityManager::class);
        $reset = new PasswordReset();
        $reset->setUser($this->user);
        $reset->setValidUntil(new DateTimeImmutable('+1 day'));
        $em->persist($reset);
        $em->flush();

        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'resetPassword', ['id' => $reset->getId()]);
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertInstanceOf(PasswordResetForm::class, $viewModel->getVariable('form'));
        $this->assertEquals('brokkr/users/account/password_reset', $viewModel->getTemplate());
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testPostEmptyResetPasswordAction()
    {
        /** @var EntityManager $em */
        $em = $this->app->getServiceManager()->get(EntityManager::class);
        $reset = new PasswordReset();
        $reset->setUser($this->user);
        $reset->setValidUntil(new DateTimeImmutable('+1 day'));
        $em->persist($reset);
        $em->flush();

        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        /** @var ViewModel $viewModel */
        $viewModel = $this->dispatch(AccountController::class, 'resetPassword', ['id' => $reset->getId()]);
        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertInstanceOf(PasswordResetForm::class, $viewModel->getVariable('form'));
        /** @var PasswordResetForm $form */
        $form = $viewModel->getVariable('form');
        $this->assertEquals(['isEmpty'], array_keys($form->getMessages('email_address')));
        $this->assertEquals(['isEmpty'], array_keys($form->getMessages('password')));
        $this->assertEquals(['isEmpty'], array_keys($form->getMessages('repeat_password')));
        $this->assertEquals('brokkr/users/account/password_reset', $viewModel->getTemplate());
    }

    public function testPostInvalidResetPasswordAction()
    {
        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $this->dispatch(
            AccountController::class,
            'resetPassword',
            ['id' => 'cce98aaa-e925-11ea-95c5-02421fe097aa']
        );
        $this->assertEquals(
            ['Password reset request not found or has expired'],
            $this->getFlashMessages(FlashMessenger::NAMESPACE_ERROR)
        );
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testPostExpiredResetPasswordAction()
    {
        /** @var EntityManager $em */
        $em = $this->app->getServiceManager()->get(EntityManager::class);
        $reset = new PasswordReset();
        $reset->setUser($this->user);
        $reset->setValidUntil(new DateTimeImmutable('-1 day'));
        $em->persist($reset);
        $em->flush();

        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $post = $this->app->getMvcEvent()->getRequest()->getPost();
        $post->set('email_address', 'test@example.com');
        $post->set('password', 'newP@ssw0rd');
        $post->set('repeat_password', 'newP@ssw0rd');

        /** @var Response $redirect */
        $this->dispatch(AccountController::class, 'resetPassword', ['id' => $reset->getId()]);
        $this->assertEquals(
            ['Password reset request not found or has expired'],
            $this->getFlashMessages(FlashMessenger::NAMESPACE_ERROR)
        );
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testPostValidResetPasswordAction()
    {
        /** @var EntityManager $em */
        $em = $this->app->getServiceManager()->get(EntityManager::class);
        $reset = new PasswordReset();
        $reset->setUser($this->user);
        $reset->setValidUntil(new DateTimeImmutable('+1 day'));
        $em->persist($reset);
        $em->flush();

        $this->app->getMvcEvent()->getRequest()->setMethod('POST');
        $post = $this->app->getMvcEvent()->getRequest()->getPost();
        $post->set('email_address', 'test@example.com');
        $post->set('password', 'newP@ssw0rd');
        $post->set('repeat_password', 'newP@ssw0rd');

        /** @var Response $redirect */
        $redirect = $this->dispatch(AccountController::class, 'resetPassword', ['id' => $reset->getId()]);
        $this->assertInstanceOf(Response::class, $redirect);
        $this->assertEquals('/login', $redirect->getHeaders()->get('Location')->getFieldValue());

        $em->refresh($this->user);
        $this->assertTrue(
            $this->user->checkPassword('newP@ssw0rd', $this->app->getServiceManager()->get('Config')['global_salt'])
        );
    }
}
