<?php

namespace monolitum\backend\params;

use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;
use monolitum\model\ValidatedValue;

class Request_PathTop_ValidatedValue extends Abstract_Request_ValidatedValue
{

    public function __construct(public readonly bool $shift)
    {
        parent::__construct(self::TYPE_STRING);
    }

    function onNotReceived(): void
    {
        throw new DevPanic("PathManager not found.");
    }

    public static function pushAndGetGetter(bool $shift): ValidatedValueGetter
    {
        $r = new self($shift);
        Monolitum::getInstance()->push($r);
        return $r;
    }

    public static function pushAndGet(bool $shift): ValidatedValue
    {
        return self::pushAndGetGetter($shift)->getValidatedValue();
    }

}
