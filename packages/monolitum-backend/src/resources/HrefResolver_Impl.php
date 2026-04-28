<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\core\MNode;

class HrefResolver_Impl implements HrefResolver
{

    /**
     * @var array<string, string>
     */
    private ?array $aloneParamValues = null;

    public function __construct(
        public readonly HrefResolverManager $manager,
        public readonly Link|Path           $link,
        public readonly bool                $obtainParamsAlone,
        public readonly bool                $isPrependHost,
        public readonly MNode               $callerNode)
    {

    }

    /**
     * @param ?array<string, string> $paramsAlone
     */
    function setAloneParamValues(?array $paramsAlone): void
    {
        $this->aloneParamValues = $paramsAlone;
    }

    /**
     * @return ?array<string, string>
     */
    public function getAloneParamValues(): ?array
    {
        return $this->aloneParamValues;
    }

    public function resolve(): string
    {
        return $this->manager->makeHref($this);
    }
}
