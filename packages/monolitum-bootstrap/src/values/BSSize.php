<?php

namespace monolitum\bootstrap\values;

class BSSize
{

    private string $value;

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function s25(): BSSize
    {
        return new BSSize("25");
    }

    public static function s50(): BSSize
    {
        return new BSSize("50");
    }

    public static function s75(): BSSize
    {
        return new BSSize("75");
    }

    public static function s100(): BSSize
    {
        return new BSSize("100");
    }

    public static function auto(): BSSize
    {
        return new BSSize("auto");
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

}
