<?php

namespace monolitum\model;

use Closure;
use monolitum\i18n\TS;

class AttrExt_Validate extends AttrExt
{

    private bool $nullable = true;

    private string|TS|null $nullableError = null;

    private bool $isDefaultSet = false;
    private mixed $def = null;
    private string|null $defStrValue = null;
    private bool $substituteNotValid = false;
    private bool $substituteNullValues = false;

    /**
     * @var Closure|null (PostValidatorContext) -> void // TODO change by context
     */
    private ?Closure $postValidatorFunction = null;

//    private $isDefaultSet = false;
//    private $def = null;
//    private $substituteNotValid = false;

    /**
     * The given function will be executed after validating nullability. And substituting values if it had a change.
     * @param Closure $validatorFunction Signature: (PostValidatorContext) -> void
     * @return $this
     */
    public function postValidate(Closure $validatorFunction): self
    {
        $this->postValidatorFunction = $validatorFunction;
        return $this;
    }

    public function nonNullable(string|TS|array|null $nullableError = null): self
    {
        $this->nullable = false;
        $this->nullableError = is_array($nullableError) ? TS::from($nullableError) : $nullableError;
        return $this;
    }

    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * @param mixed $value
     * @param bool $substituteNotValid
     * @return $this
     */
    public function replaceIf(bool $substituteNotValid, bool $substituteNullValues, mixed $value, string $strValue = null): self
    {
        $this->isDefaultSet = true;
        $this->def = $value;
        if($strValue === null){
            $this->defStrValue = strval($this->def);
        }else{
            $this->defStrValue = $strValue;
        }
        $this->substituteNotValid = $substituteNotValid;
        $this->substituteNullValues = $substituteNullValues;
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

        if($validatedValue->isValid()){

            if($validatedValue->isNull()) {
                if($this->isDefaultSet && $this->substituteNullValues){
                    $validatedValue = new ValidatedValue(true, true, $this->def, null, $this->defStrValue);
                }

                if(!$this->isNullable()) {
                    $validatedValue = new ValidatedValue(false, true, $validatedValue->getValue(), $this->nullableError, $validatedValue->getStrValue());
                }
            }

        }else if($this->isDefaultSet && $this->substituteNotValid){
            $validatedValue = new ValidatedValue(true, true, $this->def, null, $this->defStrValue);
        }

        if($validatedValue->isValid() && $this->postValidatorFunction !== null){
            $context = new PostValidatorContext($validatedValue->getValue());
            call_user_func($this->postValidatorFunction, $context);
            if(!$context->getResultValid()){
                $validatedValue = new ValidatedValue(
                    false, true,
                    $validatedValue->getValue(),
                    $context->getResultError(),
                    $validatedValue->getStrValue()
                );
            }
        }

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
