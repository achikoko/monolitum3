<?php

namespace monolitum\model;

use monolitum\i18n\TS;

class AttrExt_Validate extends AttrExt
{

    private bool $nullable = true;

    private string|TS|null $nullableError = null;

//    private $isDefaultSet = false;
//    private $def = null;
//    private $substituteNotValid = false;

    public function nonNullable(string|TS|null $nullableError = null): self
    {
        $this->nullable = false;
        $this->nullableError = $nullableError;
        return $this;
    }

    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param ValidatedValue $validatedValue
     * @return ValidatedValue
     */
    public function validate(ValidatedValue $validatedValue): ValidatedValue
    {

        if($validatedValue->isValid() && !$this->isNullable() && $validatedValue->isNull())
            return new ValidatedValue(false, true, $validatedValue->getValue(), $this->nullableError, $validatedValue->getStrValue());

        return $validatedValue;

    }

    /**
     * @return AttrExt_Validate
     */
    static function of(): static
    {
        return new static();
    }

}
