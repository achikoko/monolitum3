<?php

namespace monolitum\frontend\form;

use monolitum\model\attr\Attr;

trait Trait_Form_Validate_Attrs
{
    protected bool $validate_attrs_hasBeenSet = false;

    protected bool $validate_attrs_all = true;

    /**
     * @var array<string>
     */
    protected array $validate_attrs = [];

    /**
     * @param string ...$attrsIds
     * @return void
     */
    public function validate_all_except(...$attrsIds): void
    {
        $this->validate_attrs_hasBeenSet = true;
        $this->validate_attrs_all = true;
        $this->validate_attrs = $attrsIds;
    }

    /**
     * @param string ...$attrsIds
     * @return void
     */
    public function validate_only(...$attrsIds): void
    {
        $this->validate_attrs_hasBeenSet = true;
        $this->validate_attrs_all = false;
        $this->validate_attrs = $attrsIds;
    }

    public function isValidatable(Attr|string $attr): bool
    {
        if($attr instanceof Attr)
            $attr = $attr->getId();
        $inArray = in_array($attr, $this->validate_attrs);
        if(!($this->validate_attrs_all ^ $inArray))
            return false;
        return true;
    }

}
