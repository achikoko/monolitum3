<?php

namespace monolitum\backend\params;

use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

readonly class Request_SetRedirectPath implements MObject
{

    /**
     * @param Link|Path $linkOrPath
     */
    public function __construct(public Link|Path $linkOrPath)
    {

    }

    function onNotReceived()
    {
        throw new DevPanic("No redirect manager.");
    }
}
