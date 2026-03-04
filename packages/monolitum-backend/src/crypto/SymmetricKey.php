<?php

namespace monolitum\backend\crypto;

class SymmetricKey
{

    public function __construct(
        public string $password,
        public ?string $algorithm=null,
        public ?string $defaultInitializationVector=null)
    {

    }

}
