<?php

namespace monolitum\bootstrap\datatable;

use monolitum\backend\params\Link;

interface SortableParamsProvider
{

    function execute(DataTable $dataTable);

    function getSortedId(): ?string;
    function getSortedDesc(): bool;

    function makeSortLink(DataTable_Col $column, Link $baseLink) : Link|null;

}
