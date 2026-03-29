<?php

namespace monolitum\database;

use monolitum\model\Model;

class Query_JoinedModel
{

    private array $filters;

    /**
     * @param array $filters
     */
    public function __construct(public readonly Model|string $model, array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

}
