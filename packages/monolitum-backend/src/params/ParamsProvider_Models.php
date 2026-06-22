<?php

namespace monolitum\backend\params;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

interface ParamsProvider_Models extends ParamsProvider
{

    function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ?string;

}
