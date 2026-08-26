<?php

namespace monolitum\auth\users;

use Closure;

readonly class PermissionClosure
{
    /**
     * @param string $permissionKey
     * @param Closure $closure (AuthManager manager, Entity user) -> bool
     */
    public function __construct(
        public string $permissionKey,
        public Closure $closure,
    )
    {

    }

}
