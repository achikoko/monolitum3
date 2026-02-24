<?php
namespace monolitum\model\attr;

use monolitum\model\ValidatedValue;

class Attr_Int extends AbstractAttr
{
    #[\Override]
    public function validate($value): ValidatedValue
    {
        if(is_numeric($value)) {
            return new ValidatedValue(true, true, intval($value), null, $value);
        }
        return new ValidatedValue(false);
    }

    #[\Override]
    public function stringValue($value): string
    {
        if(is_numeric($value)) {
            return strval($value);
        }
        return "";
    }
}

