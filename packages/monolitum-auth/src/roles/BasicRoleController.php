<?php

namespace monolitum\auth\roles;

use monolitum\auth\AuthManager;

abstract class BasicRoleController implements RoleController
{

    public function __construct(public readonly string $roleType, public readonly BasicRoleModel $model)
    {

    }

    public function addBooleanPermission(): string
    {
        return $this->roleType;
    }

    public function getType(): string
    {
        return $this->roleType;
    }

    public function install(AuthManager $manager): void
    {
        $this->model->install($manager);
    }

    public function hasPermission(mixed $sessionUser, mixed $sessionRole, string $permissionKey): bool
    {
        // TODO: Implement hasPermission() method.
    }

}
