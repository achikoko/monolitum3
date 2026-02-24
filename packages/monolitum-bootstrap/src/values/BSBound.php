<?php

namespace monolitum\bootstrap\values;

class BSBound
{

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function bottom(): BSBound
    {
        return new BSBound("b");
    }

    public static function top(): BSBound
    {
        return new BSBound("t");
    }

    public static function right(): BSBound
    {
        return new BSBound("e");
    }

    public static function left(): BSBound
    {
        return new BSBound("s");
    }

    public function getValue(): string
    {
        return $this->value;
    }

}
