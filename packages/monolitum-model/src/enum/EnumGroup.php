<?php

namespace monolitum\model\enum;

use monolitum\i18n\TS;

class EnumGroup
{

    private int $indexRangeBase;
    private int $indexRangeLength;

    public function __construct(private int $index, private string|TS $label)
    {
    }

    public function setLabel(string|TS $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function setIndexRange(int $indexRangeBase, int $indexRangeLength = 0): self
    {
        $this->indexRangeBase = $indexRangeBase;
        $this->indexRangeLength = $indexRangeLength;
        return $this;
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return TS|string
     */
    public function getLabel(): string|TS
    {
        return $this->label;
    }

    /**
     * @return int
     */
    public function getIndexRangeBase(): int
    {
        return $this->indexRangeBase;
    }

    /**
     * @return int
     */
    public function getIndexRangeLength(): int
    {
        return $this->indexRangeLength;
    }

    function incrementIndexRangeLength(): void
    {
        $this->indexRangeLength++;
    }

}
