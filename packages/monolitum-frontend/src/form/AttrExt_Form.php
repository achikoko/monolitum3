<?php
namespace monolitum\frontend\form;

use monolitum\i18n\TS;
use monolitum\model\AttrExt;

class AttrExt_Form extends AttrExt
{

    private TS|string $label;

    private TS|string|null $nullLabel = null;

    private bool $isDefaultSet = false;
    private mixed $def = null;
    private bool $substituteNotValid = false;

    function label(TS|string $label): self {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string|string[] $nullLabel
     * @return $this
     */
    function nullLabel(array|string $nullLabel): self {
        $this->nullLabel = $nullLabel;
        return $this;
    }

    /**
     * @param mixed $value
     * @param bool $substituteNotValid
     * @return $this
     */
    public function def(mixed $value, bool $substituteNotValid = false): self
    {
        $this->isDefaultSet = true;
        $this->def = $value;
        $this->substituteNotValid = $substituteNotValid;
        return $this;
    }

    public function getLabel(): string|TS
    {
        return $this->label;
    }

    public function getNullLabel(): string|TS|null
    {
        return $this->nullLabel;
    }

    public function isDefaultSet(): bool
    {
        return $this->isDefaultSet;
    }

    public function getDef()
    {
        return $this->def;
    }

    /**
     * @return bool
     */
    public function isSubstituteNotValid(): bool
    {
        return $this->substituteNotValid;
    }

//    public function makeDefault(ValidatedValue $validated)
//    {
//        if(!$this->isDefaultSet)
//            return $validated;
//
//        $isValid = $validated->isValid();
//        $isNull = $validated->isNull();
//
//        if($isValid){
//            if($isNull)
//                return new ValidatedValue(true, true, $this->def);
//        }else{
//            if($this->substituteNotValid)
//                return new ValidatedValue(true, true, $this->def);
//        }
//        return $validated;
//    }


}

