<?php

namespace monolitum\bootstrap\datatable;

use Closure;
use monolitum\model\Entity;

class ManualSorter
{

    private function __construct(
        private readonly Closure $entityComparator,
    ) {

    }

    function compare(Entity $left, Entity $right): int
    {
        return call_user_func($this->entityComparator, $left, $right);
    }

    /**
     * @param Closure $entityComparator function(Entity, Entity): int
     * @return static
     */
    public static function of(Closure $entityComparator): static{
        return new self($entityComparator);
    }

    /**
     * @param Closure $valueRetriever function(Entity): mixed
     * @return static
     */
    public static function fromComparableValue(Closure $valueRetriever): static
    {
        return new self(function (Entity $left, Entity $right) use ($valueRetriever) {
            return $valueRetriever($left) <=> $valueRetriever($right);
        });
    }

}
