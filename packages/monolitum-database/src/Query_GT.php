<?php

namespace  monolitum\database;

use DateTime;

class Query_GT extends Query_CMP
{

    public function __construct(float|DateTime|int $string)
    {
        parent::__construct($string, ">");
    }

}
