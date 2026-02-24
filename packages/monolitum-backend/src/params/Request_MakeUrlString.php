<?php

namespace monolitum\backend\params;

use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\model\Model;

class Request_MakeUrlString implements MObject
{
    /**
     * @var array<string, string>
     */
    private ?array $aloneParamValues = null;

    /**
     * @var null|false|string
     */
    private string|null|false $writeAsParam = null;

    /**
     * @var bool
     */
    private bool $appendUrlPrefix = true;

    /**
     * @var array<string, Model>|null
     */
    private ?array $pushedParams = null;

    /**
     * @var string
     */
    private string $url;

    public function __construct(
        public readonly Link|Path $link,
        /**
         * if true, all params are set alone.
         * Alone params help POST forms to add hidden data into it.
         */
        public readonly bool $obtainParamsAlone = false
    ) {

    }

    public function setWriteAsParam(false|string|null $writeAsParam): self
    {
        $this->writeAsParam = $writeAsParam;
        return $this;
    }

    public function getWriteAsParam(): false|string|null
    {
        return $this->writeAsParam;
    }

    public function setAppendUrlPrefix(bool $appendUrlPrefix): self
    {
        $this->appendUrlPrefix = $appendUrlPrefix;
        return $this;
    }

    public function isAppendUrlPrefix(): bool
    {
        return $this->appendUrlPrefix;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param array<Model> $pushedParams
     * @return $this
     */
    public function addPushedParams(array $pushedParams): self
    {
        if($this->pushedParams === null){
            $this->pushedParams = [];
        }
        $this->pushedParams += $pushedParams;
        return $this;
    }

    /**
     * @return array<string, Model>|null
     */
    public function getPushedParams(): ?array
    {
        return $this->pushedParams;
    }

    /**
     * @param array<string, string> $paramsAlone
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

    function onNotReceived()
    {
        throw new DevPanic("PathManager not found.");
    }
}
