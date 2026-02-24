<?php

namespace monolitum\bootstrap\datatable;

use Closure;
use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;
use monolitum\i18n\TS;
use monolitum\model\attr\Attr;

class DataTable_Col implements MObject
{

    private string|TS $name;

    private CellRenderer|Closure|null $renderer = null;

    private bool $sortable = false;

    private string $sortable_id;

    public function __construct(string|TS $name)
    {
        $this->name = $name;
    }

    public function renderer(CellRenderer|Closure $renderer): self
    {
        $this->renderer = $renderer;
        return $this;
    }

    public function sortable(string $id): self
    {
        $this->sortable = true;
        $this->sortable_id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRenderer(): CellRenderer|Closure|null
    {
        return $this->renderer;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getSortableId(): string
    {
        return $this->sortable_id;
    }

    function onNotReceived()
    {
        throw new DevPanic();
    }

    public static function of(string|TS $name, Attr|string $attr = null): static
    {
        $datatableCol = new DataTable_Col($name);
        if($attr !== null){
            $datatableCol->renderer(CellRenderer_Attr::of($attr));
        }
        return $datatableCol;
    }

    public function pushSelf(): self
    {
        Monolitum::getInstance()->push($this);
        return $this;
    }

}
