<?php

namespace monolitum\auth\users;

use monolitum\auth\AuthManager;

interface UsersController
{
    public function install(AuthManager $manager): void;

    public function loginByCredentials(string $username, string $password): mixed;

    public function getSessionUserString(mixed $sessionUser): string;

    public function recoverSessionUser(string $sessionUserString): mixed;

    public function hasPermission(mixed $sessionUser, string $permissionKey): bool;

    public function changePassword(mixed $sessionUser, string $plainPassword): bool;

    public function getUserId(mixed $sessionUser): mixed;
}
