<?php

namespace Riddlestone\Brokkr\Users\Mvc\Controller;

use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\Controller\AbstractActionController as MvcAbstractActionController;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Riddlestone\Brokkr\Acl\Acl;
use Riddlestone\Brokkr\Acl\Exception\ResourceNotFound;
use Riddlestone\Brokkr\Acl\Exception\RoleNotFound;

abstract class AbstractActionController extends MvcAbstractActionController
{
    /**
     * @var Acl
     */
    protected $acl;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @param Acl $acl
     */
    public function setAcl(Acl $acl): void
    {
        $this->acl = $acl;
    }

    /**
     * @param AuthenticationService $authService
     */
    public function setAuthenticationService(AuthenticationService $authService): void
    {
        $this->authService = $authService;
    }

    public function forbiddenAction(): ViewModel
    {
        $this->getResponse()->setStatusCode(403);
        $viewModel = new ViewModel(['content' => 'Forbidden']);
        $viewModel->setTemplate('error/403');
        return $viewModel;
    }

    /**
     * Execute the request
     *
     * @param MvcEvent $event
     * @return mixed
     * @psalm-suppress ParamNameMismatch
     */
    public function onDispatch(MvcEvent $event)
    {
        $resource = static::class . '::' . static::getMethodFromAction($event->getRouteMatch()->getParam('action'));
        $role = $this->authService->hasIdentity()
            ? $this->authService->getIdentity()
            : null;

        try {
            if (!$this->acl->isAllowed($role, $resource)) {
                $event->getRouteMatch()->setParam('action', 'forbidden');
            }
        } catch (ResourceNotFound | RoleNotFound $exception) {
            // if role or resource is not found, allow access
        }

        return parent::onDispatch($event);
    }
}
