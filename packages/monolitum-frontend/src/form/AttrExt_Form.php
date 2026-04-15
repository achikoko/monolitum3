<?php
namespace monolitum\frontend\form;

use monolitum\i18n\TS;
use monolitum\model\AttrExt;

class AttrExt_Form extends AttrExt
{

    private TS|string|null $label = null;

    private TS|string|null $nullLabel = null;

    private bool $isDefaultSet = false;
    private mixed $def = null;
    private bool $substituteNotValid = false;
    private bool $substituteNullValues = false;

    function label(TS|array|string $label): self {
        $this->label = is_array($label) ? TS::from($label) : $label;
        return $this;
    }

    /**
     * @param string|string[] $nullLabel
     * @return $this
     */
    function nullLabel(TS|array|string $nullLabel): self {
        $this->nullLabel = is_array($nullLabel) ? TS::from($nullLabel) : $nullLabel;
        return $this;
    }

    /**
     * @param mixed $value
     * @param bool $substituteNotValid
     * @return $this
     */
    public function def(mixed $value, bool $substituteNotValid = false, bool $substituteNullValues = false): self
    {
        $this->isDefaultSet = true;
        $this->def = $value;
        $this->substituteNotValid = $substituteNotValid;
        $this->substituteNullValues = $substituteNullValues;
        return $this;
    }

    public function getLabel(): string|TS|null
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

    /**
     * @return bool
     */
    public function isSubstituteNullValues(): bool
    {
        return $this->substituteNullValues;
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

