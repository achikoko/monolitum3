<?php

namespace monolitum\database;

use monolitum\model\Model;

class _JoinsWhereHint
{

    public function __construct(public readonly Model $model, public readonly string $tableAlias)
    {

    }

    /** @var array<string, _JoinsWhereHint> */
    public array $joinsWhereHintsByAttr = [];

    /** @var array<string, _JoinsWhereHint> */
    public array $joinsWhereHintsByModel = [];

}
