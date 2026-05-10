<?php
namespace monolitum\model\attr;

use DateTime;
use DateTimeImmutable;
use monolitum\model\ValidatedValue;

class Attr_DateTime extends AbstractAttr
{

    /**
     * @param mixed $value
     * @return ValidatedValue
     */
    #[\Override]
    public function validate(mixed $value): ValidatedValue
    {
        if(is_string($value)){
            if(strlen($value) > 0){

                $date = date_create($value);
                if($date === false)
                    return new ValidatedValue(false);

                return new ValidatedValue(true, true, DateTimeImmutable::createFromMutable($date), null, $value);
            }else{
                return new ValidatedValue(true, true, null, null, "");
            }
        }else if(is_null($value)){
            return new ValidatedValue(true, true, null, null, "");
        }

        return new ValidatedValue(false);
    }

    #[\Override]
    public function stringValue(mixed $value): string
    {
        if($value instanceof DateTime){
            return $value->format('Y-m-d H-i-s');
        }
        return "";
    }

}

