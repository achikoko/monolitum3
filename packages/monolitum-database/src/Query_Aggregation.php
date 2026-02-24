<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

class Query_Aggregation extends Query
{

    const MAX = "max";
    const MIN = "min";
    const SUM = "sum";
    const COUNT = "count";

    public function __construct(DatabaseManager $manager, Model $model, public readonly Attr $selectAttr, public readonly string $operation)
    {
        parent::__construct($manager, $model);
    }

}
