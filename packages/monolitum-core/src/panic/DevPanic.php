<?php

namespace monolitum\core\panic;

use monolitum\core\MNode;

/**
 * Panic intended to represent developer mistakes in their apps.
 */
class DevPanic extends Panic{

    function __construct(string $message = null, public readonly ?MNode $node = null){
        parent::__construct($message);
    }

}
