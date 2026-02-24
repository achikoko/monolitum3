<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Path;

readonly class ResResolver_Impl implements ResResolver
{

    public function __construct(public ResResolverManager $manager, public Path $link, public bool $encodeUrl)
    {

    }

    public function resolve(): string
    {
        return $this->manager->makeRes($this);
    }
}
