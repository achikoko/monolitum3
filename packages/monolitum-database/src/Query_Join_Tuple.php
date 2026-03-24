<?php

namespace monolitum\database;

readonly class Query_Join_Tuple
{
    function __construct(public array $attrs, public bool $outer, public Query_Join $join)
    {

    }
}
