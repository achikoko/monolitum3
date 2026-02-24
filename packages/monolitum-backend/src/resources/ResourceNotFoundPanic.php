<?php

namespace monolitum\backend\resources;

use monolitum\core\panic\UserPanic;

class ResourceNotFoundPanic extends UserPanic {

    function __construct(?string $message = null){
        parent::__construct($message);
    }

}
