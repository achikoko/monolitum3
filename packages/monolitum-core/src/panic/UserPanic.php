<?php

namespace monolitum\core\panic;

class UserPanic extends Panic {

    function __construct(string $message = null){
        parent::__construct($message);
    }

}
