<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

class Query_Entities extends Query
{

    /*
     * @var string[]
     */
    private array|bool $selectAttrs;

    /**
     * @var array<Query_Sort_Tuple>
     */
    private array $sortedAttrs = [];

    private ?int $limitLow = null;
    private ?int $limitMany = null;

    public function __construct(string|Model $model, bool $defaultValueForSelect)
    {
        parent::__construct($model);
        $this->selectAttrs = $defaultValueForSelect;
    }

    /**
     * @param string|array<string>|null $attrs
     * @return $this
     */
    public function select(string|array|bool $attrs = true): self
    {
        if(is_string($attrs)) {
            $this->selectAttrs = [$attrs];
        }else { //if(is_array($attrs) || is_bool($attrs))
            $this->selectAttrs = $attrs;
        }
        return $this;
    }

    /**
     * @param int $lowOrMany
     * @param int|null $many
     * @return $this
     */
    public function limit(int $lowOrMany, int $many = null): self
    {
        if($many == null){
            $this->limitLow = 0;
            $this->limitMany = $lowOrMany;
        }else{
            $this->limitLow = $lowOrMany;
            $this->limitMany = $many;
        }
        return $this;
    }

    public function getLimitLow(): ?int
    {
        return $this->limitLow;
    }

    public function getLimitMany(): ?int
    {
        return $this->limitMany;
    }

    public function sort(string|Attr $attr, bool $desc = false, bool $promoteToGlobalDesc = null): self
    {
        $this->sortedAttrs[] = new Query_Sort_Tuple($attr, $desc, $promoteToGlobalDesc);
        return $this;
    }

    /**
     * @return array<Query_Sort_Tuple>
     */
    public function getSortedAttrs(): array
    {
        return $this->sortedAttrs;
    }

    public function getSelectAttrs(): array|bool
    {
        return $this->selectAttrs;
    }

    function hasLimit(): bool
    {
        return $this->limitLow !== null || $this->limitMany !== null;
    }

    /**
     * Returns if it has a limit of 1 and its joints as well.
     * This is for database manager to convert this join into a subquery for sorting globally by a subquery field
     * @return bool
     */
    function isLimit1Recursive(): bool
    {
        if($this->limitMany != 1)
            return false;
        if(!$this->isJoinsLimit1Recursive())
            return false;
        return true;
    }

    function isJoinsLimit1Recursive(): bool
    {
        foreach ($this->getJoins() as $join) {
            if(!$join->join->isLimit1Recursive())
                return false;
        }
        return true;
    }

    function hasPromotedSortingRecursive(): bool
    {
        foreach ($this->getSortedAttrs() as $attr) {
            if($attr->promoteToGlobalDesc !== null)
                return true;
        }
        foreach ($this->getJoins() as $join) {
            if($join->join->hasPromotedSortingRecursive())
                return true;
        }
        return false;
    }

    function hasSorting(): bool
    {
        return !empty($this->sortedAttrs);
    }

    function checkParallelSortingRecursive(): ?bool
    {
        $count = 0;
        foreach ($this->getJoins() as $join) {
            $r = $join->join->checkParallelSortingRecursive();
            if($r === null) {
                return null;
            }else if($r === true){
                $count++;
                if($count > 1)
                    return null;
            }
        }

        if($count > 0)
            return true;

        return $this->hasSorting();
    }

}
