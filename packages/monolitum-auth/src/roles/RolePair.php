<?php

namespace monolitum\auth\roles;

use monolitum\model\Entity;

class RolePair
{

    public function __construct(
        public readonly Entity $globalRole,
        public readonly Entity $typedRole
    )
    {
    }

}
