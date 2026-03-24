<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;

readonly class Query_Sort_Tuple
{
    function __construct(public string|Attr $attr, public bool $desc, public ?bool $promoteToGlobalDesc = null)
    {

    }
}
