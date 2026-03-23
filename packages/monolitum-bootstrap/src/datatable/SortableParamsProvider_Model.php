<?php

namespace monolitum\bootstrap\datatable;

use monolitum\backend\params\Link;
use monolitum\backend\params\ParamsManager;
use monolitum\model\attr\Attr;
use monolitum\model\Model;

class SortableParamsProvider_Model implements SortableParamsProvider
{

    private ?string $sortedId = null;
    private bool $sortedDesc = false;

    function __construct(
        private Model|string $model,
        private Attr|string $sortableId,
        private Attr|string|null $sortableDesc,
    )
    {

    }

    function execute(DataTable $dataTable): void
    {
        if ($this->model !== null && $this->sortableId !== null) {
            // Detect if it is sorted

            $sortValidatedValue = ParamsManager::pushGetParameterValidatedValue($this->model, $this->sortableId);

            $sortedColumnName = null;
            if ($sortValidatedValue->isValid()) {
                $this->sortedId = $sortValidatedValue->getValue();
            }

            if($this->sortedId === null)
                return;

            $descValidatedValue = $this->sortableDesc !== null ? ParamsManager::pushGetParameterValidatedValue($this->model, $this->sortableDesc) : null;

            if ($descValidatedValue->isValid() && !$descValidatedValue->isNull()) {
                $this->sortedDesc = $descValidatedValue->getValue();
            }

        }
    }

    public function makeSortLink(DataTable_Col $column, Link $baseLink): Link|null
    {
        if($column->isSortable()) {

            $myLink = $baseLink->copy();
            $myLink->addParams([
                $this->sortableId => $column->getSortableId()
            ]);
            if ($this->sortableDesc !== null) {
                if ($column->getSortableId() === $this->sortedId && !$this->sortedDesc) {
                    $myLink->addParams([
                        $this->sortableDesc => true
                    ]);
                } else {
                    $myLink->removeParams(
                        $this->sortableDesc
                    );
                }
            }

            return $myLink;
        }
        return null;
    }

    function getSortedId(): ?string
    {
        return $this->sortedId;
    }

    function getSortedDesc(): bool
    {
        return $this->sortedDesc;
    }
}
