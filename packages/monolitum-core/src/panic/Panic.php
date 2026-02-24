<?php

namespace monolitum\core\panic;

use RuntimeException;

class Panic extends RuntimeException {

    function __construct(string $message = null){
        parent::__construct($message !== null ? $message : "");
    }

}
