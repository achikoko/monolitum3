<?php

namespace monolitum\auth\roles;

use monolitum\auth\AuthManager;

interface RoleController
{
    public function getType(): string;
    public function getModelClass(): string;
    public function getAttrRoleId(): string;

    public function install(AuthManager $manager): void;

    public function hasPermission(mixed $sessionUser, RolePair $sessionRole, string $permissionKey): bool;

}
