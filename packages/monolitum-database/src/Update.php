<?php

namespace monolitum\database;

use monolitum\model\Model;

class Update extends AbstractInsertUpdate
{
    /**
     * @var array<string, mixed>
     */
    private array $filter;

    public function __construct(DatabaseManager $manager, Model $model)
    {
        parent::__construct($manager, $model);
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
     * @return int[]
     */
    public function execute(): array
    {
        return $this->manager->executeUpdate($this);
    }

}
