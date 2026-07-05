<?php

namespace monolitum\model;

use Exception;
use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;

class ModelPanic extends DevPanic
{

    function __construct(
        ?string                     $message = null,
        public readonly ?MNode     $node = null,
        ?Exception $exception = null
    ){
        parent::__construct($message, exception: $exception);
    }

}
