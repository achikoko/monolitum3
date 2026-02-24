<?php

namespace monolitum\model;

abstract class AttrExt
{

    public static function of(): static
    {
        return new static();
    }

}
