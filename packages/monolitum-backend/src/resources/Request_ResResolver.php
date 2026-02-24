<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Path;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

class Request_ResResolver implements MObject
{

    private bool $encodeUrl = true;

    private ResResolver $resResolver;

    public function __construct(public Path $path)
    {

    }

    public function setEncodeUrl(bool $encodeUrl): void
    {
        $this->encodeUrl = $encodeUrl;
    }

    public function isEncodeUrl(): bool
    {
        return $this->encodeUrl;
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function setResResolver(ResResolver $resResolver): void
    {
        $this->resResolver = $resResolver;
    }

    public function getResResolver(): ResResolver
    {
        return $this->resResolver;
    }

    function onNotReceived()
    {
        throw new DevPanic("No Active_Resolve_Res found");
    }
}
