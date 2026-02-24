<?php

namespace monolitum\bootstrap\datatable;

use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;
use monolitum\model\Entity;

interface CellRenderer
{

    function prepare(DataTable $datatable): void;

    function render(?Entity $entity): Renderable_Node|Rendered;

}
