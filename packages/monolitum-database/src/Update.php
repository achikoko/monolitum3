<?php

namespace monolitum\database;

use monolitum\model\Model;

class Update extends AbstractInsertUpdate
{
    /**
     * @var array<string, mixed>
     */
    private array $filter;

    public function __construct(string|Model $model)
    {
        parent::__construct($model);
    }

    /**
     * @param array<string, mixed> $filter
     */
    public function filter(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter(): array
    {
        return $this->filter;
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

    public static function of(string|Model $model): Update
    {
        return new Update($model);
    }

}
