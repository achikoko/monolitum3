<?php

namespace monolitum\database;

enum Operation: string
{
    case MAX = "max";
    case MIN = "min";
    case SUM = "sum";
    case COUNT = "count";

}
