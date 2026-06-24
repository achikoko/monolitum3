<?php

namespace monolitum\backend\params;

use monolitum\model\attr\Attr;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

interface ParamsProvider_Models extends ParamsProvider
{

    public function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ValidatedValue;
    public function retrieveModel(Model $model, bool $writable = false): ?Entity;

}
