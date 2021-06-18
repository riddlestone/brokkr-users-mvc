<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Unit\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Form\Element;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Riddlestone\Brokkr\Acl\Acl;
use Riddlestone\Brokkr\Users\Mvc\Controller\AccountController;
use Riddlestone\Brokkr\Users\Entity\User;
use Riddlestone\Brokkr\Users\Mvc\Form\LoginForm;
use Riddlestone\Brokkr\Users\Repository\UserRepository;
use Riddlestone\Brokkr\Users\Service\PasswordResetService;

class AccountControllerTest extends TestCase
{
    /**
     * @var MockObject|UserRepository
     */
    private $userRepository;

    /**
     * @var AuthenticationService|MockObject
     */
    private $authService;

    /**
     * @var AbstractPluginManager|MockObject
     */
    private $formElementManager;

    /**
     * @var MockObject|PasswordResetService
     */
    private $passwordResetService;

    /**
     * @var AccountController
     */
    private $controller;

    /**
     * @var PluginManager|MockObject
     */
    private $pluginManager;

    /**
     * @var FlashMessenger|MockObject
     */
    private $flashMessenger;

    /**
     * @var Redirect|MockObject
     */
    private $redirect;

    protected function setUp(): void
    {
        $this->controller = new AccountController();
        $this->controller->setAcl(
            $this->acl = $this->createMock(Acl::class)
        );
        $this->controller->setUserRepository(
            $this->userRepository = $this->createMock(UserRepository::class)
        );
        $this->controller->setAuthenticationService(
            $this->authService = $this->createMock(AuthenticationService::class)
        );
        $this->controller->setFormElementManager(
            $this->formElementManager = $this->createMock(AbstractPluginManager::class)
        );
        $this->controller->setPasswordResetService(
            $this->passwordResetService = $this->createMock(PasswordResetService::class)
        );

        $this->controller->setPluginManager(
            $this->pluginManager = $this->createMock(PluginManager::class)
        );
        $this->pluginManager
            ->method('has')
            ->willReturnCallback(function ($name) {
                return in_array($name, ['flashMessenger', 'redirect']);
            });
        $this->flashMessenger = $this->createMock(FlashMessenger::class);
        $this->redirect = $this->createMock(Redirect::class);
        $this->pluginManager
            ->method('get')
            ->willReturnMap([
                ['flashMessenger', null, $this->flashMessenger],
                ['redirect', null, $this->redirect],
            ]);
    }

    public function testIndexAction()
    {
        $user = new User();

        $this->authService
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn($user);

        $view = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $view);
        $this->assertEquals($user, $view->getVariable('user'));
        $this->assertEquals('brokkr/users/account/index', $view->getTemplate());
    }

    public function testLogoutActionSuccess()
    {
        $this->authService
            ->expects($this->once())
            ->method('hasIdentity')
            ->willReturn(true);
        $this->authService
            ->expects($this->once())
            ->method('clearIdentity');
        $this->flashMessenger
            ->expects($this->once())
            ->method('addSuccessMessage')
            ->with('Logout successful');
        $this->redirect
            ->expects($this->once())
            ->method('toRoute')
            ->with('home')
            ->willReturn($response = $this->createMock(Response::class));

        $this->assertEquals($response, $this->controller->logoutAction());
    }

    public function testLogoutActionWithoutIdentity()
    {
        $this->authService
            ->expects($this->once())
            ->method('hasIdentity')
            ->willReturn(false);
        $this->authService
            ->expects($this->never())
            ->method('clearIdentity');
        $this->flashMessenger
            ->expects($this->never())
            ->method('addSuccessMessage');
        $this->redirect
            ->expects($this->once())
            ->method('toRoute')
            ->with('home')
            ->willReturn($response = $this->createMock(Response::class));

        $this->assertEquals($response, $this->controller->logoutAction());
    }

    public function testGetLoginAction()
    {
        $form = $this->createMock(LoginForm::class);
        $this->formElementManager->method('get')
            ->with(LoginForm::class)
            ->willReturn($form);

        $response = $this->controller->loginAction();

        $this->assertInstanceOf(ViewModel::class, $response);
        $this->assertEquals('brokkr/users/account/login', $response->getTemplate());
        $this->assertEquals($form, $response->getVariable('form'));
    }

    public function testPostLoginActionSuccess()
    {
        $formData = [
            'email_address' => 'someone@example.com',
            'password' => 'myP@ssw0rd',
        ];
        $postParameters = new Parameters($formData);
        $this->controller->getRequest()->setMethod(Request::METHOD_POST);
        $this->controller->getRequest()->setPost($postParameters);
        $form = $this->createMock(LoginForm::class);
        $form->expects($this->once())
            ->method('setData')
            ->with($postParameters);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn($formData);
        $this->formElementManager->method('get')
            ->with(LoginForm::class)
            ->willReturn($form);
        $this->authService
            ->expects($this->once())
            ->method('authenticate');
        $this->authService
            ->method('hasIdentity')
            ->willReturn(true);
        $this->flashMessenger
            ->expects($this->once())
            ->method('addSuccessMessage');
        $this->redirect
            ->expects($this->once())
            ->method('toRoute')
            ->with('home')
            ->willReturn($response = $this->createMock(Response::class));

        $this->assertEquals($response, $this->controller->loginAction());
    }

    public function testPostLoginActionInvalidData()
    {
        $formData = [
            'email_address' => 'someone@example.com',
        ];
        $postParameters = new Parameters($formData);
        $this->controller->getRequest()->setMethod(Request::METHOD_POST);
        $this->controller->getRequest()->setPost($postParameters);
        $form = $this->createMock(LoginForm::class);
        $form->expects($this->once())
            ->method('setData')
            ->with($postParameters);
        $form->method('isValid')->willReturn(false);
        $form->method('getData')->willReturn($formData);
        $this->formElementManager->method('get')
            ->with(LoginForm::class)
            ->willReturn($form);
        $this->authService
            ->expects($this->never())
            ->method('authenticate');
        $this->flashMessenger
            ->expects($this->never())
            ->method('addSuccessMessage');

        /** @var ViewModel $response */
        $response = $this->controller->loginAction();
        $this->assertInstanceOf(ViewModel::class, $response);
        $this->assertEquals($form, $response->getVariable('form'));
    }

    public function testPostLoginActionIncorrectData()
    {
        $formData = [
            'email_address' => 'someone@example.com',
            'password' => 'not-my-password',
        ];
        $postParameters = new Parameters($formData);
        $this->controller->getRequest()->setMethod(Request::METHOD_POST);
        $this->controller->getRequest()->setPost($postParameters);
        $form = $this->createMock(LoginForm::class);
        $form->expects($this->once())
            ->method('setData')
            ->with($postParameters);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn($formData);
        $form->method('get')->willReturn($this->createMock(Element::class));
        $this->formElementManager->method('get')
            ->with(LoginForm::class)
            ->willReturn($form);
        $this->authService
            ->expects($this->once())
            ->method('authenticate');
        $this->authService
            ->method('hasIdentity')
            ->willReturn(false);
        $this->flashMessenger
            ->expects($this->once())
            ->method('addErrorMessage');

        /** @var ViewModel $response */
        $response = $this->controller->loginAction();
        $this->assertInstanceOf(ViewModel::class, $response);
        $this->assertEquals($form, $response->getVariable('form'));
    }
}
