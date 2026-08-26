<?php

namespace monolitum\auth;

use monolitum\model\Entity;

class Session
{
    function __construct(
        public readonly Entity $user
    )
    {

    }
}
