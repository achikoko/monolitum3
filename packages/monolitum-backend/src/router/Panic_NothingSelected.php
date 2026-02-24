<?php

namespace monolitum\backend\router;

use monolitum\core\MNode;
use monolitum\core\panic\Panic;

class Panic_NothingSelected extends Panic
{

    function __construct(string $message = null, ?MNode $node = null){
        parent::__construct($message);
    }

}
