<?php

namespace monolitum\auth\roles;

use monolitum\auth\AuthManager;

interface RolesController
{
    public function install(AuthManager $manager): void;

    /** Search in all user roles for this permission. */
    public function hasPermissionInWhateverRole(mixed $sessionUser, string $permissionKey): bool;

    /** Search for a permission in the current session role. */
    public function hasPermission(mixed $sessionUser, mixed $sessionRole, string $permissionKey): bool;

    public function recoverSessionRole(mixed $sessionUser, ?string $sessionRoleString): mixed;

    public function getSessionRoleString(mixed $sessionUser, mixed $sessionRole): ?string;

    public function switchRoleByType(mixed $sessionUser, string $roleType): mixed;
}
