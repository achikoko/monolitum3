<?php

namespace monolitum\model;

abstract class AttrExt
{

    private Attr $attr;

    /**
     * This function is called by the attribute when the extension just got added.
     * Overriders should check that the Attr type is what they expected.
     * @param Attr $attr
     * @return void
     */
    function _onSetAttr(Attr $attr): void
    {
        $this->attr = $attr;
    }

    /**
     * @return Attr
     */
    public function getAttr(): Attr
    {
        return $this->attr;
    }

    public static function of(): static
    {
        return new static();
    }

}
