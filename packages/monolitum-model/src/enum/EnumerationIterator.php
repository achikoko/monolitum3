<?php

namespace monolitum\model\enum;

use Iterator;

class EnumerationIterator implements Iterator
{

    function __construct(private readonly Enumeration $enumeration, private int $index = 0){

    }

    public function current(): mixed
    {
        return $this->enumeration->getEnumValues()[$this->index][Enumeration::ENUM_VALUE_TUPLE_LABEL];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function key(): mixed
    {
        return $this->enumeration->getEnumValues()[$this->index][Enumeration::ENUM_VALUE_TUPLE_KEY];
    }

    public function valid(): bool
    {
        // TODO: Implement valid() method.
        return $this->index < count($this->enumeration->getEnumValues());
    }

    public function rewind(): void
    {
        $this->index = 0;
    }
}
