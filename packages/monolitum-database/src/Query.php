<?php

namespace monolitum\database;

use monolitum\core\Find;
use monolitum\model\attr\Attr;
use monolitum\model\Model;

class Query
{

    /**
     * @var string[]
     */
    private ?array $selectAttrs = null;

    private array|Query_Or|null $filter = null;

    private ?int $limitLow = null;
    private ?int $limitMany = null;

    /**
     * @var array<string>
     */
    private array $sortedAttrs = [];

    /**
     * @var array<bool>
     */
    private array $sortedAttrsAsc = [];

    /**
     * @var array
     */
    private array $joins = [];

    public function __construct(public readonly DatabaseManager $manager, public readonly Model $model)
    {

    }

    /**
     * @param string|array<string>|null $attrs
     * @return $this
     */
    public function select(array|string|null $attrs): self
    {
        if(is_string($attrs))
            $this->selectAttrs = [$attrs];
        else if(is_array($attrs))
            $this->selectAttrs = $attrs;
        else
            $this->selectAttrs = null;
        return $this;
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
     * @param int $low
     * @param int|null $high
     * @return $this
     */
    public function limit(int $low, int $high = null): self
    {
        if($high == null){
            $this->limitLow = 0;
            $this->limitMany = $low;
        }else{
            $this->limitLow = $low;
            $this->limitMany = $high;
        }
        return $this;
    }

    public function sort(string|Attr $attr, bool $asc = true): self
    {
        $this->sortedAttrs[] = $attr;
        $this->sortedAttrsAsc[] = $asc;
        return $this;
    }

    /**
     * Performs an inner join in the query.
     *
     * @param string|array<string> $attrs
     * @param Join $join other query model
     * @return $this
     */
    public function join(array|string $attrs, Join $join): self
    {
        $this->joins[] = [
            "attrs" => $attrs,
            "join" => $join
        ];
        return $this;
    }

    /**
     * @return array>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @return string[]
     */
    public function getSelectAttrs(): ?array
    {
        return $this->selectAttrs;
    }

    public function getFilter(): array|Query_Or|null
    {
        return $this->filter;
    }

    public function getLimitLow(): ?int
    {
        return $this->limitLow;
    }

    public function getLimitMany(): ?int
    {
        return $this->limitMany;
    }

    public function getSortedAttrs(): array
    {
        return $this->sortedAttrs;
    }

    public function getSortedAttrsAsc(): array
    {
        return $this->sortedAttrsAsc;
    }

    public static function newQuery(string|Model $model): Query_Entities_Executor
    {
        return DatabaseManager::findSelf()->newQuery($model);
    }

    public static function newQueryAggregation(string|Model $model, string|Attr $attr, string $operation): Query_Aggregation_Executor
    {
        return DatabaseManager::findSelf()->newQuery_Aggregation($model, $attr, $operation);
    }

}
