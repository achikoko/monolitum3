<?php

namespace monolitum\backend\params;

use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

class Request_CurrentPath implements MObject
{

    public Path $path;

    /**
     * @param int $backParentsToStrip amount of parents to strip off in the path
     */
    public function __construct(public readonly int $backParentsToStrip = 0)
    {
    }

    function onNotReceived()
    {
        throw new DevPanic("PathManager not found.");
    }
}
