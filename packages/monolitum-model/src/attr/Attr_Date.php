<?php
namespace monolitum\model\attr;

use monolitum\model\ValidatedValue;

class Attr_Date extends AbstractAttr
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

                // Force to be a date, not a datetime
                $date = date_time_set($date, 0, 0);

                return new ValidatedValue(true, true, $date, null, $value);
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
        if($value instanceof \DateTime){
            return $value->format('Y-m-d');
        }
        return "";
    }

}

