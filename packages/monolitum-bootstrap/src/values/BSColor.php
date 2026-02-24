<?php

namespace monolitum\bootstrap\values;

class BSColor
{

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function primary(): BSColor
    {
        return new BSColor("primary");
    }

    public static function secondary(): BSColor
    {
        return new BSColor("secondary");
    }

    public static function success(): BSColor
    {
        return new BSColor("success");
    }

    public static function danger(): BSColor
    {
        return new BSColor("danger");
    }

    public static function warning(): BSColor
    {
        return new BSColor("warning");
    }

    public static function info(): BSColor
    {
        return new BSColor("info");
    }

    public static function light(): BSColor
    {
        return new BSColor("light");
    }

    public static function dark(): BSColor
    {
        return new BSColor("dark");
    }

    public static function body(): BSColor
    {
        return new BSColor("body");
    }

    public static function muted(): BSColor
    {
        return new BSColor("muted");
    }

    public static function white(): BSColor
    {
        return new BSColor("white");
    }

    public static function transparent(): BSColor
    {
        return new BSColor("transparent");
    }

    public function getValue(): string
    {
        return $this->value;
    }

}
