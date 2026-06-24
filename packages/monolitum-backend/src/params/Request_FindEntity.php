<?php

namespace monolitum\backend\params;

use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\model\Entity;
use monolitum\model\Model;
use function monolitum\core\m;

class Request_FindEntity implements MObject
{

    /**
     * @var Entity|null
     */
    private ?Entity $foundEntity = null;

    /**
     * @param string|Model $model
     */
    public function __construct(
        public readonly string|Model $model,
        public readonly bool $writable = false,
    ){

    }

    /**
     * @param Entity|null $foundEntity
     */
    public function setFoundEntity(?Entity $foundEntity): void
    {
        $this->foundEntity = $foundEntity;
    }

    /**
     * @return Entity|null
     */
    public function getFoundEntity(): ?Entity
    {
        return $this->foundEntity;
    }

    function onNotReceived()
    {
        throw new DevPanic("No ParamsManager received the Request.");
    }

    public static function push(string|Model $model, bool $writable = false): ?Entity
    {
        $r = new Request_FindEntity($model, $writable);
        M($r);
        return $r->getFoundEntity();
    }
}
