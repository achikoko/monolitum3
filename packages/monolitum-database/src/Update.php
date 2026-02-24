<?php

namespace monolitum\database;

use monolitum\model\Model;

class Update extends Insert
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

}
