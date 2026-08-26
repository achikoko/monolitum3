<?php

namespace monolitum\auth\users;

use monolitum\auth\AuthManager;
use monolitum\auth\Session;
use monolitum\model\Entity;

interface UsersController
{
    public function install(AuthManager $manager): void;

    public function loginByCredentials(string $username, string $password): ?Session;

    public function getSessionString(Session $session): string;

    public function recoverSession(mixed $session_string): ?Session;

    public function hasPermission(Session $session, string $permissionKey): bool;

    public function changePassword(Entity $user, string $plainPassword): bool;
}
