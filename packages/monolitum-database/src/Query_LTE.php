<?php

namespace  monolitum\database;

use DateTime;

class Query_LTE extends Query_CMP
{
    public function __construct(float|DateTime|int $value)
    {
        parent::__construct($value, "<=");
    }
}
