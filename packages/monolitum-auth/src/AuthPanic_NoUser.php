<?php
namespace monolitum\auth;

class AuthPanic_NoUser extends AuthPanic
{

    public function __construct(string $message = "No User")
    {
        parent::__construct($message);
    }

}

