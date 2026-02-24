<?php

namespace monolitum\database;

class Query_Aggregation_Executor extends Query_Aggregation
{

    public function __construct($manager, $model, $selectAttr, $operation)
    {
        parent::__construct($manager, $model, $selectAttr, $operation);
    }

    public function executeAndClose(): int|float
    {
        return $this->manager->executeQuery($this);
    }

}
