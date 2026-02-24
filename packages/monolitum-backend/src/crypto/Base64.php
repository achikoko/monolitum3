<?php

namespace monolitum\backend\crypto;

class Base64
{

    public static function encodeBase64(?string $rawData, bool $forUrl=false): ?string
    {
        if($rawData === null){
            return null;
        }
        if($forUrl){
            return rtrim( strtr( base64_encode( $rawData ), '+/', '-_'), '=');
        }else{
            return base64_encode( $rawData );
        }

    }

    public static function decodeBase64(?string $encodedData): false|string|null
    {
        if($encodedData === null){
            return null;
        }
        return base64_decode( strtr($encodedData , '-_', '+/'));
    }


}
