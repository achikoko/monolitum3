<?php
namespace monolitum\model;

use monolitum\core\panic\DevPanic;
use monolitum\model\attr\Attr;

class AnonymousModel
{

    /**
     * @var array<string, Attr>
     */
    protected array $attrs = [];

    /**
     * @param string $attrId
     * @param Attr $attr
     */
    public function attr(string $attrId, Attr $attr): void
    {
        $attr->_setModelId($this, $attrId);
        if(key_exists($attrId, $this->attrs))
            throw new DevPanic("Id $attrId already exists in " . $this->__toString());
        $this->attrs[$attrId] = $attr;
    }

    /**
     * @param string|Attr $attrId
     * @return Attr
     *@throws DevPanic if attribute not found in model
     */
    public function getAttr(Attr|string $attrId): Attr
    {
        if($attrId instanceof Attr){
            if(!key_exists($attrId->getId(), $this->attrs))
                throw new DevPanic("Attr $attrId of Model $this not found.");
            return $attrId;
        }

        if(!key_exists($attrId, $this->attrs))
            throw new DevPanic("Attr $attrId of Model $this not found.");

        return $this->attrs[$attrId];
    }

    /**
     * @return array<Attr>
     */
    public function getAttrs(): array
    {
        return array_values($this->attrs);
    }

    public function __toString()
    {
        return "Anonymous Model";
    }

    /**
     * @param string|Attr $attr
     * @return bool
     */
    public function hasAttr(Attr|string $attr): bool
    {
        if($attr instanceof Attr)
            return in_array($attr, $this->attrs);

        foreach ($this->attrs as $attr2){
            if($attr2->getId() == $attr)
                return true;
        }

        return false;
    }
}

