<?php

namespace monolitum\backend\params;

use monolitum\core\panic\DevPanic;

enum Source: string
{
    case GET = "GET";
    case POST = "POST";
//    case SESSION;
//    case COOKIE;

    static function get(): array
    {
        return [
            self::GET->name => self::GET,
            self::POST->name => self::POST
        ];
    }

    /**
     * @throws DevPanic
     */
    public function toGlobalArray(): array
    {
        return match ($this) {
            self::GET => $_GET,
            self::POST => $_POST,
            default => throw new DevPanic("Not supported source."),
        };
    }

}
