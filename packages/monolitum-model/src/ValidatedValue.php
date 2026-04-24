<?php

namespace monolitum\model;

use monolitum\i18n\TS;

class ValidatedValue
{

    private bool $isValid;
    private bool $isWellFormat;
    private mixed $value;

    private string|TS|null $error;

    private ?string $strValue;

    /*
     * TODO make this constructor private and create factory methods
     */
    public function __construct(bool $isValid=false, bool $wellFormat=false, mixed $value = null, string|TS|array|null $error = null, ?string $strValue = null)
    {
        $this->isValid = $isValid;
        $this->isWellFormat = $wellFormat;
        $this->value = $value;
        $this->error = is_array($error) ? TS::from($error) : $error;
//        if($strValue !== null){
            $this->strValue = $strValue;
//        }else if($value !== null){
//            if($value instanceof \DateTime){
//                $this->strValue = $value->format('Y-m-d H:i:s');
//            }else{
//                $this->strValue = strval($value);
//            }
//        }else{
//            $this->strValue = "";
//        }
    }

    /**
     * @return mixed
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @return mixed|null
     */
    public function getValue(bool $substituteIfNotValid = false, mixed $valueIfNotValid = null): mixed
    {
        return $substituteIfNotValid && !$this->isValid ? $valueIfNotValid : $this->value;
    }

    /**
     * @return string|null
     */
    public function getStrValue()
    {
        return $this->strValue;
    }

    /**
     * @return mixed|null
     */
    public function getError()
    {
        return $this->error;
    }

    public function isNull()
    {
        return $this->value === null;
    }

    public function isWellFormat()
    {
        return $this->isWellFormat;
    }

}
