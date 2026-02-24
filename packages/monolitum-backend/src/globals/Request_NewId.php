<?php

namespace monolitum\backend\globals;

use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;

class Request_NewId implements MObject
{

    private string $id;

    public function __construct(public readonly ?string $contextIds)
    {

    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    function onNotReceived()
    {
        throw new DevPanic();
    }

    public static function pushAndGet(?string $contextIds = null): string
    {
        $request = new Request_NewId($contextIds);
        Monolitum::getInstance()->push($request);
        return $request->getId();
    }

}
