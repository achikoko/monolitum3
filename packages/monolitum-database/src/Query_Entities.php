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

    public function sort(string|Attr $attr, bool $asc = true): self
    {
        $this->sortedAttrs[] = new Query_Sort_Tuple($attr, $asc);
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

}
