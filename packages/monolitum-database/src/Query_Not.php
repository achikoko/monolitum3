<?php

namespace monolitum\database;

class Query_Not
{

    private mixed $filter;

    /**
     * @param mixed $filter
     */
    public function __construct(mixed $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return mixed
     */
    public function getFilter(): mixed
    {
        return $this->filter;
    }

}
