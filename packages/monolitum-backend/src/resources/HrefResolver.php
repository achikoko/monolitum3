<?php

namespace monolitum\backend\resources;

interface HrefResolver
{

    public function resolve(): string;

    /**
     * If the user requested to get the params alone, this method will return them.
     * @return array<string, string>|null
     */
    public function getAloneParamValues(): ?array;

}
