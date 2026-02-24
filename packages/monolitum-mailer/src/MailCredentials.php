<?php

namespace monolitum\mailer;

readonly class MailCredentials
{

    public function __construct(
        public string $host,
        public string $address,
        public string $name,
        public string $password
    )
    {
    }

}
