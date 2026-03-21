<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

class Query
{

    private array|Query_Or|null $filter = null;

    /**
     * @var array
     */
    private array $joins = [];

    public function __construct(public readonly string|Model $model)
    {

    }

    /**
     * @param array<string, mixed>|Query_Or|null $filter
     * @return $this
     */
    public function filter(array|Query_Or|null $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Performs an outer join in the query. (Skips rows without relations).
     *
     * @param string|array<string> $attrs
     * @param Query_Join $join other query model
     * @return $this
     */
    public function join(array|string $attrs, Query_Join $join): self
    {
        if(is_string($attrs)){
            $attrs = [$attrs];
        }
        $this->joins[] = new Query_Join_Tuple($attrs, false, $join);
        return $this;
    }

    /**
     * Performs an inner join in the query. (Returns entities even if there is not a single relation)
     *
     * @param string|array<string> $attrs
     * @param Query_Join $join other query model
     * @return $this
     */
    public function innerJoin(array|string $attrs, Query_Join $join): self
    {
        $this->joins[] = new Query_Join_Tuple(is_string($attrs) ? [$attrs] : $attrs, true, $join);
        return $this;
    }

    /**
     * @return array<Query_Join_Tuple>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    public function hasJoins(): bool
    {
        return !empty($this->joins);
    }

    public function getFilter(): array|Query_Or|null
    {
        return $this->filter;
    }

    public static function newQuery(string|Model $model): Query_Entities_Executor
    {
        return new Query_Entities_Executor($model);
    }

    public static function newQueryAggregation(string|Model $model, string|Attr $attr, Operation $operation): Query_Aggregation_Executor
    {
        return new Query_Aggregation_Executor($model, $attr, $operation);
    }

    public static function newQueryJoin(string|Model $model, array|string $attrs): Query_Join
    {
        return new Query_Join($model, $attrs);
    }

}
