<?php

namespace Riddlestone\Brokkr\Users\Mvc\Controller;

use Exception;
use Laminas\Form\ElementInterface;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\View\Model\ViewModel;
use Riddlestone\Brokkr\Users\Authentication\AuthenticationAdapter;
use Riddlestone\Brokkr\Users\Mvc\Form\LoginForm;
use Riddlestone\Brokkr\Users\Mvc\Form\PasswordResetForm;
use Riddlestone\Brokkr\Users\Mvc\Form\RequestPasswordResetForm;
use Riddlestone\Brokkr\Users\Repository\UserRepository;
use Riddlestone\Brokkr\Users\Service\PasswordResetService;

/**
 * Class AccountController
 *
 * @package Riddlestone\Brokkr\Users
 * @method FlashMessenger flashMessenger()
 */
class AccountController extends AbstractActionController
{
    /**
     * @var UserRepository|null
     */
    protected $userRepository;

    /**
     * @var AbstractPluginManager|null
     */
    protected $formElementManager;

    /**
     * @var PasswordResetService|null
     */
    protected $passwordResetService;

    /**
     * @param UserRepository $userRepository
     */
    public function setUserRepository(UserRepository $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param AbstractPluginManager $formElementManager
     */
    public function setFormElementManager(AbstractPluginManager $formElementManager): void
    {
        $this->formElementManager = $formElementManager;
    }

    /**
     * @param PasswordResetService $passwordResetService
     */
    public function setPasswordResetService(PasswordResetService $passwordResetService): void
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        $viewModel = new ViewModel(
            [
                'user' => $this->authService->getIdentity(),
            ]
        );
        $viewModel->setTemplate('brokkr/users/account/index');
        return $viewModel;
    }

    /**
     * @return Response
     */
    public function logoutAction()
    {
        if ($this->authService->hasIdentity()) {
            $this->authService->clearIdentity();
            if ($this->plugins->has('flashMessenger')) {
                $this->flashMessenger()->addSuccessMessage('Logout successful');
            }
        }
        return $this->redirect()->toRoute('home');
    }

    /**
     * @return Response|ViewModel
     */
    public function loginAction()
    {
        /** @var LoginForm $form */
        $form = $this->formElementManager->get(LoginForm::class);
        $viewModel = new ViewModel(['form' => $form]);
        $viewModel->setTemplate('brokkr/users/account/login');
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            $data = $form->getData();
            $this->authService->authenticate(
                new AuthenticationAdapter($this->userRepository, $data['email_address'], $data['password'])
            );
            if ($this->authService->hasIdentity()) {
                if ($this->plugins->has('flashMessenger')) {
                    $this->flashMessenger()->addSuccessMessage('Login successful');
                }
                return $this->redirect()->toRoute('home');
            }
            $this->addFormErrorMessage('Email address or password incorrect', $form->get('email_address'));
        }
        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     * @throws Exception
     */
    public function requestPasswordResetAction()
    {
        /** @var RequestPasswordResetForm $form */
        $form = $this->formElementManager->get(RequestPasswordResetForm::class);
        $viewModel = new ViewModel(['form' => $form]);
        $viewModel->setTemplate('brokkr/users/account/request_password_reset');
        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            $data = $form->getData();
            $user = $this->userRepository->findOneByEmailAddress($data['email_address']);
            if ($user) {
                $this->passwordResetService->createReset($user);
            }
            if ($this->plugins->has('flashMessenger')) {
                $this->flashMessenger()->addSuccessMessage('Password reset sent');
            }
            return $this->redirect()->toRoute('home');
        }
        return $viewModel;
    }

    /**
     * @return Response|ViewModel
     * @throws Exception
     */
    public function resetPasswordAction()
    {
        try {
            $reset = $this->passwordResetService->getReset($this->params('id'));
        } catch (Exception $e) {
            if ($this->plugins->has('flashMessenger')) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
            return $this->redirect()->toRoute('home');
        }
        /** @var PasswordResetForm $form */
        $form = $this->formElementManager->get(
            PasswordResetForm::class,
            [
                'email_address' => $reset->getUser()->getEmailAddress(),
            ]
        );
        $viewModel = new ViewModel(['form' => $form]);
        $viewModel->setTemplate('brokkr/users/account/password_reset');
        if ($this->getRequest()->isPost()) {
            $form->setData($this->params()->fromPost());
            if (!$form->isValid()) {
                return $viewModel;
            }
            $data = $form->getData();
            try {
                $this->passwordResetService->processReset($this->params('id'), $data['password']);
            } catch (Exception $e) {
                if ($this->plugins->has('flashMessenger')) {
                    $this->flashMessenger()->addErrorMessage($e->getMessage());
                }
                return $viewModel;
            }
            if ($this->plugins->has('flashMessenger')) {
                $this->flashMessenger()->addSuccessMessage('Password reset, please login');
            }
            return $this->redirect()->toRoute('brokkr-users:login');
        }
        return $viewModel;
    }

    /**
     * Sets an error message through flashMessenger if available, or on the form element if not
     *
     * @param string $message
     * @param ElementInterface $element
     * @return void
     */
    protected function addFormErrorMessage(string $message, ElementInterface $element): void
    {
        if ($this->plugins->has('flashMessenger')) {
            $this->flashMessenger()->addErrorMessage($message);
            return;
        }
        $element->setMessages(array_merge($element->getMessages(), ['Email address or password incorrect']));
    }
}
