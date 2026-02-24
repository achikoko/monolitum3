<?php
namespace monolitum\model\attr;

use monolitum\model\ValidatedValue;

class Attr_Bool extends AbstractAttr
{

    #[\Override]
    public function validate($value): ValidatedValue
    {
        if(is_string($value)){
            if($value == "true"){
                return new ValidatedValue(true, true,true, null, $value);
            }else if($value == "false"){
                return new ValidatedValue(true, true, false, null, $value);
            }else{
                return new ValidatedValue(true, true,true, null, "true");
            }
        }else if(is_numeric($value)){
            if($value == 0) {
                return new ValidatedValue(true, true,false, null, "false");
            }else {
                return new ValidatedValue(true, true,true, null, "true");
            }
        }else if(is_null($value)){
            return new ValidatedValue(true, true,false, null, "false");
        }
        return new ValidatedValue(false);
    }

    #[\Override]
    public function stringValue($value): string
    {
        if(is_bool($value)){
            return $value ? "true" : "false";
        }
        return "";
    }
}

