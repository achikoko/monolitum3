<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

class Query_Aggregation_Executor extends Query_Aggregation
{

    public function __construct(string|Model $model, string|Attr $selectAttr, Operation $operation)
    {
        parent::__construct($model, $selectAttr, $operation);
    }

    public function executeAndClose(?DatabaseManager $databaseManager = null): int|float
    {
        if($databaseManager === null){
            $databaseManager = DatabaseManager::findSelf();
        }
        return $databaseManager->executeQuery($this);
    }

}
