<?php

namespace monolitum\bootstrap\datatable;

use monolitum\bootstrap\values\BSColor;

class TableRow
{

    private ?BSColor $configuratedColor = null;

    public function __construct(private readonly int $index, private readonly mixed $entity, private readonly array $row){

    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getRowData(): mixed
    {
        return $this->entity;
    }

    public function color(BSColor $color): self
    {
        $this->configuratedColor = $color;
        return $this;
    }

    function getRow(): array
    {
        return $this->row;
    }

    function getConfiguratedColor(): ?BSColor
    {
        return $this->configuratedColor;
    }

}
