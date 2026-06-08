<?php

namespace monolitum\database;

use monolitum\model\Model;

class Query_Entities_Executor extends Query_Entities
{

    private bool $forUpdate = false;

    public function __construct(string|Model $model)
    {
        parent::__construct($model, true);
    }
    public function execute(?DatabaseManager $databaseManager = null): Query_Result
    {
        if($databaseManager === null){
            $databaseManager = DatabaseManager::findSelf();
        }
        return $databaseManager->executeQuery($this);
    }

    /**
     * Store entities in the manager to be referenced later
     */
    public function store(): self
    {
        return $this;
    }

    public function forUpdate($writable = true): self
    {
        $this->forUpdate = $writable;
        return $this;
    }

    public function isForUpdate(): bool
    {
        return $this->forUpdate;
    }

}
