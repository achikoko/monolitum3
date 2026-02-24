<?php

namespace monolitum\bootstrap\values;

class BSAxis
{

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function x(): BSAxis
    {
        return new BSAxis("x");
    }

    public static function y(): BSAxis
    {
        return new BSAxis("y");
    }

    public function getValue(): string
    {
        return $this->value;
    }

}
