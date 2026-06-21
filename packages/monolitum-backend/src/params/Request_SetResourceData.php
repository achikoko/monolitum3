<?php

namespace monolitum\backend\params;

use Closure;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

readonly class Request_SetResourceData implements MObject
{

    private function __construct(public ?string $dataBase64, public ?Closure $writerFunction, public bool $writeMime, public ?string $mimeType)
    {

    }

    function onNotReceived()
    {
        throw new DevPanic("No redirect manager.");
    }

    public static function fromBase64Data(string $dataBase64): Request_SetResourceData
    {
        return new Request_SetResourceData($dataBase64, null, true, null);
    }


    public static function fromWriterFunction(callable $writerFunction, bool $writeDefaultMime = true): Request_SetResourceData
    {
        return new Request_SetResourceData(null, $writerFunction, $writeDefaultMime, null);
    }

    public static function fromWriterFunctionWithMime(callable $writerFunction, string $mimeType): Request_SetResourceData
    {
        return new Request_SetResourceData(null, $writerFunction, true, $mimeType);
    }

}
