<?php

namespace monolitum\backend\params;

use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use function monolitum\core\m;

class Request_FindParameters implements MObject
{

    /**
     * @var array<string, string>
     */
    private array $foundParams = [];

    /**
     * @param string|string[] $providerOrProviders
     * @param bool|string[] $paramsSelection null for all params in category, empty array for no parameters
     * @param string[]|null $exceptions if all requested, exception list
     */
    public function __construct(
        public readonly array|string $providerOrProviders,
        public readonly ?array       $paramsSelection,
        public readonly ?array       $exceptions
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
        throw new DevPanic("No ParamsManager received the Request.");
    }

    public static function push(array|string $providerOrProviders, ?array $paramsSelection, ?array $exceptions): array
    {
        $r = new Request_FindParameters($providerOrProviders, $paramsSelection, $exceptions);
        M($r);
        return $r->getFoundParams();
    }

}
