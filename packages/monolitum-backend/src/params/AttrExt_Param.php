<?php

namespace monolitum\backend\params;

use monolitum\model\AttrExt;

class AttrExt_Param extends AttrExt
{

    private string $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

}
