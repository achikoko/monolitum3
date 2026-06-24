<?php

namespace monolitum\database;

use monolitum\model\Model;

class Insert extends AbstractInsertUpdate
{

    private bool $upsert = false;

    public function __construct(DatabaseManager $manager, Model $model)
    {
        parent::__construct($manager, $model);
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
     * @return int[]
     */
    public function execute(): array
    {
        return $this->manager->executeUpdate($this);
    }

}
