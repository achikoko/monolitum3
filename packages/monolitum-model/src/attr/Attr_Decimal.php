<?php
namespace monolitum\model\attr;

use Exception;
use monolitum\model\ValidatedValue;

class Attr_Decimal extends AbstractAttr
{
    private int $decimals;

    /**
     * @param int $decimals
     */
    public function __construct(int $decimals = 0)
    {
        $this->decimals = $decimals;
    }

    /**
     * @param int $decimals
     */
    public function setDecimals(int $decimals): self
    {
        $this->decimals = $decimals;
        return $this;
    }

    /**
     * @return int
     */
    public function getDecimals(): int
    {
        return $this->decimals;
    }

    #[\Override]
    public function validate(mixed $value): ValidatedValue
    {
        if(is_numeric($value)){
            $withoutPoint = intval(intval($value) * pow(10, $this->decimals));
            return new ValidatedValue(true, true, $withoutPoint, null, $this->stringValue($value));
        } else if(is_string($value)){
            try{
                $floatValue = floatval($value);
                $intValue = intval($floatValue * pow(10, $this->decimals));
                return new ValidatedValue(true, true, $intValue, null, $this->stringValue($intValue));
            }catch (Exception $e){
                return new ValidatedValue(false);
            }
        }
        return new ValidatedValue(false);
    }

    #[\Override]
    public function stringValue(mixed $value): string
    {
        if(is_int($value)){
            $zeros = pow(10, $this->decimals);
            $integerPart = intval($value / $zeros);
            $floatingPart = $value - ($integerPart * $zeros);
            return $integerPart . "." . $floatingPart;
        }
        return "";
    }

    public static function fromDecimals(int $decimals = 0): Attr_Decimal{
        return new Attr_Decimal($decimals);
    }

}

