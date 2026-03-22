<?php

namespace monolitum\core\panic;

use Exception;
use RuntimeException;

class Panic extends RuntimeException {

    function __construct(string $message = null, Exception $exception = null) {
        parent::__construct($message !== null ? $message : "", previous: $exception);
    }

}
