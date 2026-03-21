<?php

namespace monolitum\database;

use monolitum\model\Model;

class Query_Entities_Executor extends Query_Entities
{

    private bool $forUpdate = false;

    private ?int $limitLow = null;
    private ?int $limitMany = null;

    public function __construct(string|Model $model)
    {
        parent::__construct($model, true);
    }

    /**
     * @param int $low
     * @param int|null $high
     * @return $this
     */
    public function limit(int $low, int $high = null): self
    {
        if($high == null){
            $this->limitLow = 0;
            $this->limitMany = $low;
        }else{
            $this->limitLow = $low;
            $this->limitMany = $high;
        }
        return $this;
    }

    public function execute(?DatabaseManager $databaseManager = null): Query_Result
    {
        if($databaseManager === null){
            $databaseManager = DatabaseManager::findSelf();
        }
        return $databaseManager->executeQuery($this);
    }

    public function getLimitLow(): ?int
    {
        return $this->limitLow;
    }

    public function getLimitMany(): ?int
    {
        return $this->limitMany;
    }

    /**
     * Store entities in the manager to be referenced later
     */
    public function store(): self
    {
        return $this;
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
