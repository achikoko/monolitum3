<?php

namespace monolitum\bootstrap\values;

class BSWeight
{

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function bold(): BSWeight
    {
        return new BSWeight("bold");
    }

    public static function bolder(): BSWeight
    {
        return new BSWeight("bolder");
    }

    public static function semibold(): BSWeight
    {
        return new BSWeight("semibold");
    }

    public static function normal(): BSWeight
    {
        return new BSWeight("normal");
    }

    public static function light(): BSWeight
    {
        return new BSWeight("light");
    }

    public static function lighter(): BSWeight
    {
        return new BSWeight("lighter");
    }

    public function getValue(): string
    {
        return $this->value;
    }

}
