<?php

namespace monolitum\model\values;

readonly class File
{

    public function __construct(public string $name, public string $type, public int $size, public string $tempName)
    {

    }


}
