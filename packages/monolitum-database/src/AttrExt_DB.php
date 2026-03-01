<?php
namespace monolitum\database;

use monolitum\model\attr\Attr;
use monolitum\model\AttrExt;
use monolitum\model\Model;

class AttrExt_DB extends AttrExt
{

    private bool $primaryKey = false;

    private bool $autoincrement = false;

    private string|Model|null $foreignModel = null;
    private string|Attr|null $foreignAttr = null;

    private bool $isDefaultSet = false;
    private mixed $def = null;

    public function primaryKey(): self
    {
        $this->primaryKey = true;
        return $this;
    }

    public function autoincrementPrimaryKey(): self
    {
        $this->primaryKey = true;
        $this->autoincrement = true;
        return $this;
    }

    public function foreignOf(Model|string $model, Attr|string $attr): self
    {
        $this->foreignModel = $model;
        $this->foreignAttr = $attr;
        return $this;
    }

    /**
     * @param mixed $value
     * @param bool $substituteNotValid
     * @return $this
     */
    public function def(mixed $value): self
    {
        $this->isDefaultSet = true;
        $this->def = $value;
        return $this;
    }

    public function isPrimaryKey(): bool
    {
        return $this->primaryKey;
    }

    public function isAutoincrement(): bool
    {
        return $this->autoincrement;
    }

    public function getForeignModel(): Model|string|null
    {
        return $this->foreignModel;
    }

    public function getForeignAttr(): Attr|string|null
    {
        return $this->foreignAttr;
    }

    public function isDefaultSet(): bool
    {
        return $this->isDefaultSet;
    }

    public function getDef()
    {
        return $this->def;
    }

}

