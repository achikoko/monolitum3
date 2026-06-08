<?php

namespace monolitum\database;

use monolitum\model\Model;

class Insert extends AbstractInsertUpdate
{

    public function __construct(DatabaseManager $manager, Model $model)
    {
        parent::__construct($manager, $model);
    }

    /**
     * @return int[]
     */
    public function execute(): array
    {
        return $this->manager->executeUpdate($this);
    }

}
