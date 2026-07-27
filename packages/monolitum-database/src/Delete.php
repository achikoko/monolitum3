<?php

namespace monolitum\database;

use monolitum\model\Model;

class Delete
{

    /**
     * @var array<string, mixed>
     */
    private array $filter;

    public function __construct(public readonly string|Model $model)
    {

    }

    /**
     * @param array<string, mixed> $filter
     * @return $this
     */
    public function filter(array $filter): self
    {
        $this->filter = $filter;
        return $this;
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

    public function getFilter(): array
    {
        return $this->filter;
    }

    public static function of(string|Model $model): Delete
    {
        return new Delete($model);
    }

}
