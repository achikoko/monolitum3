<?php

namespace monolitum\backend\params;

enum Source: string
{
    case GET = "GET";
    case POST = "POST";
//    case SESSION;
//    case COOKIE;

    static function get(): array
    {
        return [
            self::GET => self::GET,
            self::POST => self::POST
        ];
    }

}
