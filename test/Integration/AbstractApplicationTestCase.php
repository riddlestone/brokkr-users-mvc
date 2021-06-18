<?php

namespace Riddlestone\Brokkr\Users\Mvc\Test\Integration;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Laminas\Authentication\Adapter\AdapterInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Result;
use Laminas\Mvc\Application;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Router\Http\RouteMatch;
use Laminas\Uri\Http;
use PHPUnit\Framework\TestCase;
use Riddlestone\Brokkr\Users\Entity\User;

abstract class AbstractApplicationTestCase extends TestCase
{
    /**
     * @var Application
     */
    protected $app;

    protected function getAppConfig(): array
    {
        $config = require __DIR__ . '/config/application.config.php';
        return [
            'modules' => array_merge($config['modules'], [
                'Laminas\Form',
                'Laminas\Router',
                'Laminas\Mvc\Plugin\FlashMessenger',
                'Riddlestone\Brokkr\Users\Mvc',
            ]),
            'module_listener_options' => array_merge($config['module_listener_options'], [
                'config_glob_paths' => array_merge($config['module_listener_options']['config_glob_paths'], [
                    __DIR__ . '/config/local.config.php',
                ]),
            ]),
        ];
    }

    /**
     * @throws ToolsException
     * @throws ORMException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = Application::init($this->getAppConfig());
        /** @var EntityManager $em */
        $em = $this->app->getServiceManager()->get(EntityManager::class);
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($em->getMetadataFactory()->getAllMetadata());

        $this->getAuthenticationService()->clearIdentity();
    }

    /**
     * @return AuthenticationService
     */
    protected function getAuthenticationService()
    {
        return $this->app->getServiceManager()->get(AuthenticationService::class);
    }

    protected function createAuthAdapter(User $user)
    {
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('authenticate')->willReturn(new Result(Result::SUCCESS, $user));
        return $adapter;
    }

    protected function authenticate(User $user)
    {
        $this->getAuthenticationService()->authenticate($this->createAuthAdapter($user));
    }

    protected function dispatch(string $controllerName, string $actionName, array $params = [])
    {
        $this->app->getServiceManager()->get('Router')->setRequestUri(new Http('https://example.com'));
        $this->app->getMvcEvent()->setRouteMatch(
            new RouteMatch(array_merge(['controller' => $controllerName, 'action' => $actionName], $params))
        );
        $controller = $this->app->getServiceManager()
            ->get('ControllerManager')
            ->get($controllerName);
        $controller->setEvent($this->app->getMvcEvent());
        return $controller->dispatch(
            $this->app->getMvcEvent()->getRequest(),
            $this->app->getMvcEvent()->getResponse()
        );
    }

    /**
     * @param string $namespace
     * @return array
     */
    protected function getFlashMessages(string $namespace): array
    {
        /** @var PluginManager $pluginManager */
        $pluginManager = $this->app->getServiceManager()->get(PluginManager::class);
        /** @var FlashMessenger $flashMessenger */
        $flashMessenger = $pluginManager->get('flashMessenger');
        return $flashMessenger->getCurrentMessages($namespace);
    }
}
