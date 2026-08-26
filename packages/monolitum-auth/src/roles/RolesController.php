<?php

namespace monolitum\auth\roles;

use monolitum\auth\AuthManager;
use monolitum\auth\Session;

interface RolesController
{
    public function install(AuthManager $manager): void;

    public function hasPermission(Session $session, string $permissionKey): bool;
}
