<?php

namespace monolitum\database;

use monolitum\model\Model;

class Query_Entities_Executor extends Query_Entities
{

    private bool $forUpdate = false;

    public function __construct(DatabaseManager $manager, Model $model)
    {
        parent::__construct($manager, $model);
    }

    public function execute(): Query_Result
    {
        return $this->manager->executeQuery($this);
    }

    public function forUpdate(): self
    {
        $this->forUpdate = true;
        return $this;
    }

    public function isForUpdate(): bool
    {
        return $this->forUpdate;
    }

}
