<?php

namespace monolitum\database;

use monolitum\model\Model;

class Delete
{

    /**
     * @var array<string, mixed>
     */
    private array $filter;

    public function __construct(public readonly DatabaseManager $manager, public readonly Model $model)
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

    public function execute(): int
    {
        return $this->manager->executeUpdate($this)[0];
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

}
