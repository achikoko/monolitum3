<?php

namespace monolitum\database;

use monolitum\model\Model;

class Insert extends AbstractInsertUpdate
{

    private bool $upsert = false;

    public function __construct(string|Model $model)
    {
        parent::__construct($model);
    }

    public function upsert(bool $upsert = true): self
    {
        $this->upsert = $upsert;
        return $this;
    }

    public function getUpsert(): bool
    {
        return $this->upsert;
    }

    /**
     * @param DatabaseManager|null $databaseManager
     * @return int[]
     */
    public function execute(?DatabaseManager $databaseManager = null): array
    {
        if($databaseManager === null){
            $databaseManager = DatabaseManager::findSelf();
        }
        return $databaseManager->executeUpdate($this);
    }

    public static function of(string|Model $model): Insert
    {
        return new Insert($model);
    }

}
