<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

class Query_Aggregation extends Query
{

    public function __construct(string|Model $model, public readonly string|Attr $selectAttr, public readonly Operation $operation)
    {
        parent::__construct($model);
    }

}
