<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

class Request_HrefResolver implements MObject
{

    private HrefResolver $hrefResolver;

    private bool $setParamsAlone = false;

    private bool $prependHost = false;

    /**
     * @param Link|Path $link
     */
    public function __construct(public Link|Path $link)
    {

    }

    public function isSetParamsAlone(): bool
    {
        return $this->setParamsAlone;
    }

    public function setHrefResolver(HrefResolver $hrefResolver): Request_HrefResolver
    {
        $this->hrefResolver = $hrefResolver;
        return $this;
    }

    public function setPrependHost(bool $prependHost=true): Request_HrefResolver
    {
        $this->prependHost = $prependHost;
        return $this;
    }

    public function getHrefResolver(): HrefResolver
    {
        return $this->hrefResolver;
    }

    public function isPrependHost(): bool
    {
        return $this->prependHost;
    }

    function onNotReceived()
    {
        throw new DevPanic("No HrefProvider found");
    }

    /**
* //     * TODO Comment out support for this. (All parameters in links are GET)
* //     * TODO Forms must query POST if they want and set appropriate hidden fields.
     * @param bool|array<string, string> $setParamsAlone
     * @return void
     */
    public function setParamsAlone(array|bool $setParamsAlone = true): void
    {
        $this->setParamsAlone = $setParamsAlone;
    }
}
