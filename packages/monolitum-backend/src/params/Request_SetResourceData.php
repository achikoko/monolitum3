<?php

namespace monolitum\backend\params;

use Closure;
use monolitum\core\Active;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

readonly class Request_SetResourceData implements MObject
{

    private function __construct(public ?string $dataBase64, public ?Closure $writerFunction)
    {

    }

    function onNotReceived()
    {
        throw new DevPanic("No redirect manager.");
    }

    /**
     * @param string $dataBase64
     * @return Request_SetResourceData
     */
    public static function fromBase64Data(string $dataBase64): Request_SetResourceData
    {
        return new Request_SetResourceData($dataBase64, null);
    }

    /**
     * @param callable $writerFunction
     * @return Request_SetResourceData
     */
    public static function fromWriterFunction(callable $writerFunction): Request_SetResourceData
    {
        return new Request_SetResourceData(null, $writerFunction);
    }
}
