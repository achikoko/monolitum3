<?php

namespace monolitum\database;

use monolitum\model\Model;

class Query_Entities extends Query
{

    /*
     * @var string[]
     */
    private ?array $selectAttrs = null;

    public function __construct(DatabaseManager $manager, Model $model)
    {
        parent::__construct($manager, $model);
    }

    /**
     * @param string|array<string>|null $attrs
     * @return $this
     */
    public function select(string|array|null $attrs): self
    {
        if(is_string($attrs))
            $this->selectAttrs = [$attrs];
        else if(is_array($attrs))
            $this->selectAttrs = $attrs;
        else
            $this->selectAttrs = null;
        return $this;
    }

    /**
     * Store entities in the manager to be referenced later
     */
    public function store(): self
    {
        return $this;
    }

    public function getSelectAttrs(): ?array
    {
        return $this->selectAttrs;
    }

}
