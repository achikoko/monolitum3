<?php
namespace monolitum\model;

use monolitum\i18n\TS;

class AttrExt_Validate_Int extends AttrExt_Validate
{

    private ?int $min = null;

    private TS|string|null $minError = null;

    private ?int $max = null;

    private TS|string|null $maxError = null;

    private bool $adjustMinMaxIfInvalid = false;

    public function adjustMinMaxIfInvalid($adjustMinMaxIfInvalid = true): self
    {
        $this->adjustMinMaxIfInvalid = $adjustMinMaxIfInvalid;
        return $this;
    }

    /**
     * @param int $int
     * @param string|TS|null $minError
     * @return $this
     */
    public function min(int $int, string|TS $minError = null): self
    {
        $this->min = $int;
        $this->minError = $minError;
        return $this;
    }

    /**
     * @param int $int
     * @param string|TS|null $maxError
     * @return $this
     */
    public function max(int $int, string|TS $maxError = null): self
    {
        $this->max = $int;
        $this->maxError = $maxError;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMin(): ?int
    {
        return $this->min;
    }

    /**
     * @return int|null
     */
    public function getMax(): ?int
    {
        return $this->max;
    }

    #[\Override]
    public function validate(ValidatedValue $validatedValue): ValidatedValue
    {
        $validatedValue = parent::validate($validatedValue);

        if(!$validatedValue->isValid())
            return $validatedValue;

        $error = false;
        $errorMessage = null;

        if(!$validatedValue->isNull()){

            /** @var int $val */
            $val = $validatedValue->getValue();

            if($this->min !== null && $val < $this->min){
                if($this->adjustMinMaxIfInvalid){
                    return new ValidatedValue(true, true, $this->min, null, strval($this->min));
                }
                $error = true;
                $errorMessage = $this->minError;
            }

            if($this->max !== null && $val > $this->max){
                if($this->adjustMinMaxIfInvalid){
                    return new ValidatedValue(true, true, $this->max, null, strval($this->max));
                }
                $error = true;
                $errorMessage = $this->maxError;
            }

        }

        if($error){
            return new ValidatedValue(false, true, $validatedValue->getValue(), $errorMessage, $validatedValue->getStrValue());
        }else{
            return $validatedValue;
        }

    }

    public static function from(): AttrExt_Validate_Int
    {
        return new AttrExt_Validate_Int();
    }

}

