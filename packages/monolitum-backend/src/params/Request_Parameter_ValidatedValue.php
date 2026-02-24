<?php

namespace monolitum\backend\params;

use monolitum\core\Monolitum;
use monolitum\model\attr\Attr;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

class Request_Parameter_ValidatedValue extends Abstract_Request_ValidatedValue
{
    /**
     * @param string $type
     * @param string|Model $model
     * @param string|Attr $attr
     */
    public function __construct(string $type, public readonly Model|string $model, public readonly Attr|string $attr)
    {
        parent::__construct($type);
    }

    public static function pushAndGet(Model|string $model, Attr|string $attr): ValidatedValue
    {
        $r = new self(Abstract_Request_ValidatedValue::TYPE_STRING, $model, $attr);
        Monolitum::getInstance()->push($r);
        return $r->getValidatedValue();
    }

    public static function pushAndGetGetter(Model|string $model, Attr|string $attr): ValidatedValueGetter
    {
        $r = new self(Abstract_Request_ValidatedValue::TYPE_STRING, $model, $attr);
        Monolitum::getInstance()->push($r);
        return $r;
    }

}
