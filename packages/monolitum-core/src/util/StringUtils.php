<?php

namespace monolitum\core\util;

class StringUtils
{

    public static function toIdentifier(string $string, string $prependIfStartsInNumber = null): string
    {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s-]/", "_", $string);
        // If string starts in number, add underscore
        if($prependIfStartsInNumber !== null && preg_match("/$([0-9])/", $string)) {
            $string = $prependIfStartsInNumber . $string;
        }
        return $string;

    }

    public static function seoUrl($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }

    public static function serializeArrayToParams(array $array): string
    {
        return http_build_query($array);
    }

    public static function deserializeParamsToArray(string $string): array
    {
        parse_str($string, $array);
        return $array;
    }

}
