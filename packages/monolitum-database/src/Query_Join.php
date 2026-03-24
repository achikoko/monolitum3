<?php

namespace monolitum\database;

use monolitum\core\Find;
use monolitum\model\Model;

class Query_Join extends Query_Entities
{

    private array $localJointAttrs;

    public function __construct(string|Model $model, array|string $attrs)
    {
        parent::__construct($model, false);
        $this->localJointAttrs = is_string($attrs) ? [$attrs] : $attrs;
    }

    public function getLocalJointAttrs(): array
    {
        return $this->localJointAttrs;
    }

    /** @noinspection PhpRedundantMethodOverrideInspection */
    public function isLimit1Recursive(): bool
    {
        // TODO autodetect implicit limit 1, when the local join attrs are a complete key (in such case, a subquery is not necessary)
        return parent::isLimit1Recursive();
    }

}
