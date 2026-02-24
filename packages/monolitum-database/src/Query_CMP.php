<?php

namespace monolitum\database;

use DateTime;

class Query_CMP
{

    protected function __construct(public readonly float|DateTime|int $value, public readonly string $sign)
    {

    }

}
