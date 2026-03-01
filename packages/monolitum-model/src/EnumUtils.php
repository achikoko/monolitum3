<?php

namespace monolitum\model;

use Generator;
use monolitum\core\panic\DevPanic;
use monolitum\i18n\TS;

enum EnumUtils
{

    public static function getKeyFromEnumArrayItem(mixed $itemKey, mixed $itemValue): string
    {

        if (is_string($itemKey)) {
            return $itemKey;
        } else if (is_array($itemValue)) {
            return $itemValue[0];
        }else{
            throw new DevPanic("Enum constant not found");
        }

    }

    public static function getValueFromEnumArrayItem(mixed $itemKey, mixed $itemValue): mixed
    {

        if(is_string($itemValue)){
            return $itemValue;
        }else if(is_array($itemValue)){
            return $itemValue[1];
        }else{
            throw new DevPanic("Enum constant not found");
        }
    }

    public static function getValueFromEnumArray(?array $enums, mixed $itemKey): mixed
    {

        if($enums !== null){
            foreach ($enums as $enumKey => $enumValue){
                if(is_string($enumKey)){
                    // Case [KEY => VALUE]
                    if($itemKey == $enumKey){
                        return $enumValue;
                    }
                }else if(is_string($enumValue)){
                    // Case [KEY...]
                    if($itemKey == $enumValue){
                        return $enumValue;
                    }
                }else if(is_array($enumValue)){
                    // Case [[KEY, VALUE]...]
                    if($itemKey == $enumValue[0]){
                        return  $enumValue[1];
                    }
                }else{
                    throw new DevPanic("Enum constant not found");
                }
            }
        }

        return null;
    }

    public static function getStringFromEnumArray(?array $enums, mixed $itemKey): string|TS|null
    {
        $value = self::getValueFromEnumArray($enums, $itemKey);

        if(is_string($value) || $value instanceof TS){
            return $value;
        }

        return null;
    }

    public static function iterateKeys(array|string|null $enums): Generator
    {

        if($enums !== null){
            foreach ($enums as $enumKey => $enumValue){
                if(is_string($enumKey)){
                    // Case [KEY => VALUE]
                    yield $enumKey;
                }else if(is_string($enumValue)){
                    // Case [KEY...]
                    yield $enumValue;
                }else if(is_array($enumValue)){
                    // Case [[KEY, VALUE]...]
                    yield $enumValue[0];
                }else{
                    throw new DevPanic("Enum constant not found");
                }
            }
        }
    }

}
