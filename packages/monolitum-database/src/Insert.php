<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

class Insert
{

    /**
     * @var array<string, mixed>
     */
    private array $values = [];

    public function __construct(public readonly DatabaseManager $manager, public readonly Model $model)
    {

    }

    public function addValue(string|Attr $attr, mixed $value): self
    {
        if($attr instanceof Attr)
            $this->values[$attr->getId()] = $value;
        else
            $this->values[$attr] = $value;
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return int[]
     */
    public function execute(): array
    {
        return $this->manager->executeUpdate($this);
    }

}
