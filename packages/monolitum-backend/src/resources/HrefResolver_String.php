<?php

namespace monolitum\backend\resources;

readonly class HrefResolver_String implements HrefResolver
{

    /**
     * @param $string
     */
    public function __construct(public string $string)
    {
    }

    function resolve(): string
    {
        return $this->string;
    }

    function getAloneParamValues(): ?array
    {
        return null;
    }
}
