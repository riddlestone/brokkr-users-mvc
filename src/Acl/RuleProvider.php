<?php

namespace Riddlestone\Brokkr\Users\Mvc\Acl;

use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;
use Riddlestone\Brokkr\Acl\GenericRule;
use Riddlestone\Brokkr\Acl\PluginManager\RuleProviderInterface;
use Riddlestone\Brokkr\Acl\RuleInterface;
use Riddlestone\Brokkr\Users\Mvc\Controller\AccountController;
use Riddlestone\Brokkr\Users\Entity\User;

class RuleProvider implements RuleProviderInterface
{
    /**
     * @param RoleInterface[] $roles
     * @param ResourceInterface[] $resources
     * @return RuleInterface[]
     */
    public function getRules(array $roles, array $resources)
    {
        $rules = [];
        $roleIds = array_map(function (?RoleInterface $role) {
            return $role
                ? $role->getRoleId()
                : null;
        }, $roles);
        $resourceIds = array_map(function (?ResourceInterface $resource) {
            return $resource
                ? $resource->getResourceId()
                : null;
        }, $resources);

        if (
            in_array(null, $roleIds)
            && in_array(AccountController::class . '::loginAction', $resourceIds)
        ) {
            $rules[] = new GenericRule(Acl::TYPE_ALLOW, null, AccountController::class . '::loginAction');
        }
        if (
            in_array(User::class, $roleIds)
            && in_array(AccountController::class . '::loginAction', $resourceIds)
        ) {
            $rules[] = new GenericRule(Acl::TYPE_DENY, User::class, AccountController::class . '::loginAction');
        }
        if (
            in_array(User::class, $roleIds)
            && in_array(AccountController::class . '::logoutAction', $resourceIds)
        ) {
            $rules[] = new GenericRule(Acl::TYPE_ALLOW, User::class, AccountController::class . '::logoutAction');
        }

        return $rules;
    }
}
