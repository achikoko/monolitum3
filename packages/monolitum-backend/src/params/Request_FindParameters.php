<?php

namespace monolitum\backend\params;

use monolitum\core\Active;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

class Request_FindParameters implements MObject
{

    const CATEGORY_GET = "get";
    const CATEGORY_POST = "post";
    //const SESSION = "session";
    //const COOKIE = "cookie";

    /**
     * @var array<string, string>
     */
    private array $foundParams = [];

    /**
     * @param string|string[] $category
     * @param bool|string[] $paramsSelection null for all params in category, empty array for no parameters
     * @param string[]|null $exceptions if all requested, exception list
     */
    public function __construct(
        public readonly array|string $category,
        public readonly ?array $paramsSelection,
        public readonly ?array $exceptions
    ){

    }

    /**
     * @param array<string, string> $currentParams
     */
    public function setFoundParams(array $currentParams): void
    {
        $this->foundParams = $currentParams;
    }

    /**
     * @return array<string, string>
     */
    public function getFoundParams(): array
    {
        return $this->foundParams;
    }

    function onNotReceived()
    {
        throw new DevPanic("No Params manager.");
    }
}
