<?php

namespace monolitum\auth\roles;

use Closure;

readonly class RolePermissionClosure
{
    /**
     * @param string $permissionKey
     * @param Closure $closure (AuthManager manager, Entity $user, Entity $global, Entity $specific) -> bool
     */
    public function __construct(
        public string $permissionKey,
        public Closure $closure,
    )
    {

    }

}
