<?php
namespace monolitum\model\attr;

use monolitum\model\ValidatedValue;
use monolitum\model\values\Color;

class Attr_Color extends AbstractAttr
{

    #[\Override]
    public function validate(mixed $value): ValidatedValue
    {
        if(is_string($value)){
            $parsed = Color::fromHex($value);
            if($parsed !== null){
                return new ValidatedValue(true, true, strlen($value) == 0 ? null : Color::fromHex($value), null, $value);
            }else if($value === "true" || $value === "false"){
                return new ValidatedValue(true, true, $value === "true" ? Color::white() : Color::black(), null, $value);
            }else if(is_numeric($value)){
                $hex = str_pad(dechex(intval($value)), 8, '0', STR_PAD_LEFT);
                return new ValidatedValue(true, true, Color::fromHex($hex), null, $value);
            }
        }else if(is_null($value)){
            return new ValidatedValue(true, true, null, null, "null");
        }
        return new ValidatedValue(false);
    }

    #[\Override]
    public function stringValue(mixed $value): string
    {
        if($value instanceof Color){
            return $value->getHexValue();
        }
        return "";
    }

}

