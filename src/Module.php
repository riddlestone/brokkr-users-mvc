<?php

namespace Riddlestone\Brokkr\Users\Mvc;

use Laminas\Authentication\AuthenticationService;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Helper\Navigation;
use Laminas\View\Renderer\PhpRenderer;
use Riddlestone\Brokkr\Acl\Acl;

class Module
{
    public function getConfig()
    {
        return require __DIR__ . '/../config/module.config.php';
    }

    /**
     * Register {@link onRender} with the application event manager
     *
     * @param MvcEvent $event
     * @return void
     */
    public function onBootstrap(MvcEvent $event): void
    {
        $event->getApplication()->getEventManager()->attach('render', [$this, 'onRender'], 10);
    }

    /**
     * Inject the current authenticated user into {@link Navigation} when rendering
     *
     * @param MvcEvent $event
     * @return void
     */
    public function onRender(MvcEvent $event)
    {
        $serviceManager = $event->getApplication()->getServiceManager();

        if (!$serviceManager->has('ViewRenderer')) {
            return;
        }

        $renderer = $serviceManager->get('ViewRenderer');

        if (!$renderer instanceof PhpRenderer) {
            return;
        }

        if (!$renderer->getHelperPluginManager()->has('navigation')) {
            return;
        }

        $navigation = $renderer->getHelperPluginManager()->get('navigation');

        if (! $navigation instanceof Navigation) {
            return;
        }

        /** @var AuthenticationService $auth */
        $auth = $serviceManager->get(AuthenticationService::class);

        /** @var string $role */
        $role = $auth->getIdentity();

        /** @var Acl $acl */
        $acl = $serviceManager->get(Acl::class);

        if (! $acl->hasRole($role)) {
            $role = null;
        }

        $navigation->setDefaultAcl($acl);
        $navigation->setDefaultRole($role);
    }
}
